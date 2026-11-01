"""Export the local PHP site's public UI for GitHub Pages.

Requires a running local XAMPP site and: python -m pip install beautifulsoup4
Run from the repository: python scripts/build-pages.py
"""
import argparse
import json
import re
import shutil
import subprocess
import urllib.request
from pathlib import Path
from urllib.parse import parse_qs, unquote, urlencode, urlsplit, urlunsplit

from bs4 import BeautifulSoup

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--project', type=Path, default=Path(__file__).resolve().parent.parent)
parser.add_argument('--output', type=Path)
parser.add_argument('--base-url', default='http://localhost/wolf-to-wow/')
parser.add_argument('--php', default=shutil.which('php') or 'C:/xampp/php/php.exe')
args = parser.parse_args()
project = args.project.resolve()
output = (args.output or project / 'docs').resolve()
output.mkdir(parents=True, exist_ok=True)
base = args.base_url.rstrip('/') + '/'

# Export only the public service catalog, never customer records or credentials.
php = '''session_save_path(sys_get_temp_dir()); require %s;
echo json_encode([
 'services'=>grooming_services(), 'sizes'=>size_labels(),
 'periods'=>daycare_periods(), 'daycareRates'=>daycare_rates(),
 'slots'=>grooming_time_slots(),
 'groomers'=>array_map(fn($g)=>['name'=>$g['GRM_GroomerName'],
 'hours'=>(int)$g['GRM_HoursPerWeek'],'factor'=>groomer_multiplier($g['GRM_ID'])],get_groomers()),
 'events'=>get_events()]);''' % json.dumps((project / 'app.php').as_posix())
result = subprocess.run([args.php, '-r', php], check=True, capture_output=True, text=True, encoding='utf-8')
catalog = json.loads(result.stdout)
if not catalog['events'] and (project / 'content/Events.txt').read_text(encoding='utf-8').strip():
    raise RuntimeError('Event catalog could not be read by PHP; check local file permissions before exporting.')

def fetch(route):
    with urllib.request.urlopen(base + route, timeout=20) as response:
        html = response.read().decode('utf-8')
    if 'Fatal error' in html or '<html' not in html.lower():
        raise RuntimeError('Could not render public page: ' + route)
    return BeautifulSoup(html, 'html.parser')

def fragment(html):
    return BeautifulSoup(html, 'html.parser')

def rewrite_link(value):
    url = urlsplit(value)
    if url.scheme or url.netloc:
        return value
    name = url.path.removeprefix('./')
    query = parse_qs(url.query)
    if name == 'event.php':
        slug = query.get('event', [''])[0]
        if slug not in {e['slug'] for e in catalog['events']}:
            return 'events.html'
        return 'event-' + slug + '.html'
    if name in ('account.php', 'staff.php', 'logout.php') or name.startswith('booknow_process'):
        return 'demo.html#staff' if name == 'staff.php' else 'demo.html#customer'
    if name.endswith('.php'):
        name = name[:-4] + '.html'
        if name in ('login.html', 'register.html'):
            return name + ('#' + url.fragment if url.fragment else '')
        return urlunsplit(('', '', name, url.query, url.fragment))
    return value

def prepare(soup, title=None):
    if title:
        soup.title.string = title + ' | From Wolf to Wow'
    for tag in soup.select('[href]'):
        tag['href'] = rewrite_link(tag['href'])
    account_nav = soup.select_one('.account-nav')
    if account_nav:
        account_nav.clear()
        account_nav.append(fragment('<a class="button button-small button-coral" href="booknow.html">Try Booking</a><a class="ghost-link" href="demo.html">Backend Demos</a>'))
    notice = soup.select_one('.notice-bar')
    if notice:
        notice.clear()
        notice.append(fragment('Portfolio preview · Explore the frontend. <a href="demo.html">Watch the real booking &amp; approval workflow →</a>'))
    css = soup.new_tag('link', rel='stylesheet', href='assets/portfolio.css')
    soup.head.append(css)
    script = soup.new_tag('script', src='assets/preview.js', defer=True)
    soup.head.append(script)
    bottom = soup.select_one('.footer-bottom')
    if bottom:
        bottom.append(fragment('<a href="demo.html#backend">Backend workflow</a>'))
    for form in soup.select('form'):
        form['action'] = '#'
        form['onsubmit'] = 'return false;'
        form.attrs.pop('method', None)
        form['data-preview-form'] = ''
        for input_tag in form.select('input, select, textarea'):
            input_tag.attrs.pop('name', None)
        note = fragment('<p class="preview-note span-2">Form preview only. <a href="demo.html">Watch the working PHP backend</a>.</p>')
        form.insert(0, note)
        for button in form.select('button[type="submit"]'):
            button['type'] = 'button'
            button['data-preview-action'] = ''
    return soup

def copy_asset(value):
    url = urlsplit(value)
    if url.scheme or url.netloc:
        return
    relative = unquote(url.path).removeprefix('./')
    if not relative or relative.startswith(('assets/', 'media/', 'diagrams/')):
        return
    src = (project / relative).resolve()
    if not src.is_relative_to(project):
        raise RuntimeError('Asset outside project: ' + value)
    if not src.is_file():
        raise FileNotFoundError(src)
    dest = output / relative
    dest.parent.mkdir(parents=True, exist_ok=True)
    if src != dest.resolve():
        shutil.copy2(src, dest)

def save(soup, filename):
    for tag in soup.select('img[src], link[rel="icon"], link[rel="apple-touch-icon"], link[rel="stylesheet"]'):
        copy_asset(tag.get('src') or tag['href'])
    html = '<!doctype html>\n' + str(soup.html)
    if re.search(r'(?:href|src|action)=["\'][^"\']*\.php', html, re.I):
        raise RuntimeError('Unconverted PHP URL in ' + filename)
    if '192.168.' in html or '<?php' in html:
        raise RuntimeError('Local dependency in ' + filename)
    (output / filename).write_text(html, encoding='utf-8')

pages = ['index', 'aboutus', 'grooming', 'daycare', 'gallery', 'events', 'products',
         'contact', 'login', 'register', 'forgot_password']
for page in pages:
    soup = prepare(fetch(page + '.php'))
    if page == 'products':
        for index, ad in enumerate(soup.select('iframe')):
            label = 'Square' if index == 0 else 'Banner'
            ad.replace_with(fragment(f'<div class="sample-ad"><small>{label} advertising preview</small><strong>More good days for your dog.</strong><p>A sample placement for the future storefront.</p><a href="grooming.html">Explore grooming →</a></div>'))
    if page == 'contact':
        for p in soup.find_all('p'):
            if 'Messages are saved even' in p.get_text():
                p.string = 'This static preview does not send messages. The PHP application stores contact messages and tracks the email-sending result.'
    save(soup, page + '.html')

for event in catalog['events']:
    soup = prepare(fetch('event.php?' + urlencode({'event': event['slug']})))
    save(soup, 'event-' + event['slug'] + '.html')

booking = prepare(fetch('booknow.php'), 'Booking Preview')
booking.main.clear()
booking.main.append(fragment('''
<section class="band band-white"><div class="band-inner">
  <span class="eyebrow">Interactive booking preview</span>
  <h1>Choose an available spot first.</h1>
  <p class="text-measure">Try service selection, dates and instant pricing with sample availability. No booking is submitted. <a href="demo.html#customer">Watch a real request being saved →</a></p>
  <div class="booking-tabs" aria-label="Booking service type" id="booking-tabs"></div>
  <div class="date-strip" aria-label="Booking date selection" id="date-strip"></div>
  <div id="booking-results"></div>
  <section class="form-surface booking-confirm" id="confirm-booking" hidden></section>
  <noscript><p>Please enable JavaScript to try the price calculator. You can also <a href="demo.html">watch the recorded backend demos</a>.</p></noscript>
</div></section>
'''))
# Remove the original PHP-rendered booking JavaScript; the preview has no login dependency.
for script in booking.find_all('script'):
    if not script.get('src'):
        script.decompose()
data = booking.new_tag('script', id='booking-catalog', type='application/json')
data.string = json.dumps({k: v for k, v in catalog.items() if k != 'events'}, ensure_ascii=False).replace('<', '\\u003c')
booking.main.append(data)
booking.main.append(booking.new_tag('script', src='assets/booking.js', defer=True))
save(booking, 'booknow.html')

demo = prepare(fetch('index.php'), 'Backend Walkthroughs')
demo.main.clear()
demo.main.append(fragment('''
<section class="band band-white"><div class="band-inner">
  <span class="eyebrow">The application in action</span>
  <h1>From a customer request<br>to staff confirmation.</h1>
  <p class="text-measure">These recordings show the real PHP/MySQL application running locally with fictional customer data. Browse the static frontend, then see what happens behind the forms.</p>
  <div class="button-row"><a class="button button-coral" href="#customer">Customer booking</a><a class="button button-outline" href="#staff">Staff approval</a><a class="button button-outline" href="#backend">Backend logic</a></div>
</div></section>
<section class="band band-warm" id="customer"><div class="band-inner video-section">
  <div class="section-heading"><div><span class="eyebrow">01 · Customer · 36 seconds</span><h2>Request a grooming visit.</h2></div><p>Desktop date, groomer and service selection, price estimate, and a saved grooming request.</p></div>
  <video controls playsinline preload="metadata" poster="media/customer-booking-poster.jpg?v=2" aria-label="Customer booking walkthrough"><source src="media/customer-booking.mp4?v=2" type="video/mp4">Your browser does not support embedded video. <a href="media/customer-booking.mp4?v=2">Download the recording</a>.</video>
  <p class="fine-print">Recorded sequence: choose a grooming date and time → enter dog details → select a service → review the estimate → submit → see the saved request.</p>
  <a class="text-link" href="media/customer-booking.mp4?v=2" download>Download customer recording</a>
</div></section>
<section class="band band-white" id="staff"><div class="band-inner video-section">
  <div class="section-heading"><div><span class="eyebrow">02 · Staff · 11 seconds</span><h2>Review and confirm a request.</h2></div><p>The staff dashboard loads saved requests and updates a pending booking after the administrator saves an action.</p></div>
  <video controls playsinline preload="metadata" poster="media/staff-approval-poster.jpg" aria-label="Staff approval walkthrough"><source src="media/staff-approval.mp4" type="video/mp4">Your browser does not support embedded video. <a href="media/staff-approval.mp4">Download the recording</a>.</video>
  <p class="fine-print">Recorded sequence: review the pending daycare request → choose an action → save → receive confirmation of the database update.</p>
  <a class="text-link" href="media/staff-approval.mp4" download>Download staff recording</a>
</div></section>
<section class="band band-warm" id="backend"><div class="band-inner">
  <span class="eyebrow">Behind the interface</span><h2>One connected backend workflow.</h2>
  <p class="text-measure">Account permissions, booking validation, pricing, capacity, approval and cancellation, plus contact messages and password recovery.</p>
  <a class="diagram-link" href="diagrams/backend-flow.svg" target="_blank" rel="noopener"><img src="diagrams/backend-flow.svg" alt="Complete backend workflow covering accounts, booking checks, pricing, staff review, cancellations, email and persistence" loading="lazy"></a>
  <div class="button-row"><a class="button button-outline" href="diagrams/backend-flow.svg" target="_blank" rel="noopener">Open full-size diagram</a><a class="button button-outline" href="login.html">Login screen</a><a class="button button-outline" href="register.html">Registration screen</a></div>
</div></section>
'''))
save(demo, 'demo.html')

diagram = project / 'docs/diagrams/backend-flow.svg'
destination = output / 'diagrams/backend-flow.svg'
destination.parent.mkdir(parents=True, exist_ok=True)
if diagram.resolve() != destination.resolve():
    shutil.copy2(diagram, destination)
(output / '.nojekyll').write_text('', encoding='utf-8')
(output / '.htaccess').write_text('DirectoryIndex index.html\n', encoding='utf-8')
print(f'Exported {len(pages) + len(catalog["events"]) + 2} HTML pages to {output}')
