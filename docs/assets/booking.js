(() => {
  'use strict';
  const catalog = JSON.parse(document.getElementById('booking-catalog').textContent);
  const params = new URLSearchParams(location.search);
  const money = value => new Intl.NumberFormat('en-CA', {style:'currency', currency:'CAD'}).format(value);
  const escape = value => String(value).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const iso = date => `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
  const today = new Date(); today.setHours(12,0,0,0);
  const dates = Array.from({length:15}, (_,i) => {const d=new Date(today); d.setDate(d.getDate()+i); return d;});
  const weekend = date => [0,6].includes(date.getDay());
  const selectedDate = dates.find(d => iso(d)===params.get('date') && !weekend(d)) || dates.find(d => !weekend(d));
  const state = {service:params.get('service')==='daycare'?'daycare':'grooming', date:iso(selectedDate), groomer:null, time:null, period:null};
  const tabs = document.getElementById('booking-tabs');
  const dateStrip = document.getElementById('date-strip');
  const results = document.getElementById('booking-results');
  const confirm = document.getElementById('confirm-booking');
  const dateLabel = () => new Date(state.date+'T12:00:00').toLocaleDateString('en-CA',{weekday:'long',month:'long',day:'numeric'});
  const options = (values, selected='S') => Object.entries(values).map(([key,label]) => `<option value="${escape(key)}" ${key===selected?'selected':''}>${escape(label)}</option>`).join('');
  const available = (groomer,time) => !(groomer===0 && time===Object.keys(catalog.slots)[0]);
  const spots = period => ({MORNING:12,AFTERNOON:14,FULL:12}[period]);

  function syncUrl() {
    const url = new URL(location.href);
    url.search = new URLSearchParams({service:state.service,date:state.date}).toString();
    history.replaceState(null,'',url);
  }
  function render() {
    tabs.innerHTML = ['grooming','daycare'].map(service => `<a href="booknow.html?service=${service}&date=${state.date}" data-service="${service}" class="${service===state.service?'is-active':''}" ${service===state.service?'aria-current="page"':''}>${service==='grooming'?'Grooming':'Daycare'}</a>`).join('');
    dateStrip.innerHTML = dates.map(date => `<button type="button" data-date="${iso(date)}" class="date-pill ${iso(date)===state.date?'is-active':''} ${weekend(date)?'is-disabled':''}" ${weekend(date)?'disabled aria-label="Closed on weekends"':''} aria-pressed="${iso(date)===state.date}"><strong>${date.toLocaleDateString('en-CA',{month:'short',day:'numeric'})}</strong><span>${date.toLocaleDateString('en-CA',{weekday:'short'})}</span></button>`).join('');
    confirm.hidden = true;
    if(state.service==='grooming') {
      results.innerHTML = `<div class="booking-panel" id="booking-availability"><h2>Grooming availability for ${escape(dateLabel())}</h2><p class="fine-print">Sample availability · pick an available slot to try the estimate.</p><table class="availability-table"><thead><tr><th>Start time</th>${catalog.groomers.map(g=>`<th title="${escape(g.name)} · ${g.hours} hours/week · rate factor ${g.factor}">${escape(g.name)}</th>`).join('')}</tr></thead><tbody>${Object.entries(catalog.slots).map(([time,label])=>`<tr><th>${escape(label)}</th>${catalog.groomers.map((g,i)=>`<td data-label="${escape(g.name)}">${available(i,time)?`<button class="slot-button" type="button" data-groomer="${i}" data-time="${time}">Available</button>`:'<span class="slot-unavailable">Sample booked</span>'}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`;
    } else {
      results.innerHTML = `<div class="daycare-grid" id="booking-availability">${Object.entries(catalog.periods).map(([key,period])=>`<article class="period-card"><span class="spots">${spots(key)} sample spots left</span><h3>${escape(period.label)}</h3><p>${escape(period.time)}</p><button type="button" class="button button-coral" data-period="${key}">Select ${escape(period.label)}</button></article>`).join('')}</div><p class="fine-print">Example availability: full-day visits count toward both half-day capacities.</p>`;
    }
    syncUrl();
  }
  function showForm() {
    const grooming = state.service==='grooming';
    const groomer = grooming ? catalog.groomers[state.groomer] : null;
    const summary = grooming ? `${groomer.name} · ${catalog.slots[state.time]}` : `${catalog.periods[state.period].label} · ${catalog.periods[state.period].time}`;
    confirm.innerHTML = `<h2>${grooming?'Grooming':'Daycare'} request preview</h2><p>${escape(summary)} on ${escape(dateLabel())}.</p><form id="${grooming?'grooming':'daycare'}-form" class="form-grid" onsubmit="return false;">
      <div class="field span-2"><label for="preview-saved-dog">Use sample dog details</label><select id="preview-saved-dog"><option value="manual">Manual entry</option><option value="mochi">Mochi · fictional sample</option></select></div>
      <div class="field"><label for="${grooming?'dog_name':'daycare_dog_name'}">Dog name</label><input id="${grooming?'dog_name':'daycare_dog_name'}" type="text" maxlength="50" required placeholder="Your dog's name"></div>
      <div class="field"><label for="${grooming?'dog_size':'daycare_dog_size'}">Dog size</label><select id="${grooming?'dog_size':'daycare_dog_size'}" required>${options(catalog.sizes)}</select></div>
      ${grooming?`<div class="field span-2"><fieldset class="preview-services"><legend>Service</legend><div class="service-option-grid">${Object.entries(catalog.services).map(([key,service],i)=>`<label class="service-option ${i===0?'is-selected':''}"><input type="radio" name="preview_service" value="${escape(key)}" ${i===0?'checked':''}><span class="service-option-copy"><strong>${escape(service.label)}</strong><small>From ${money(Math.min(...Object.values(service.rates)))}</small></span></label>`).join('')}</div></fieldset></div>`:`<div class="field"><label for="preview-human-name">Your name</label><input id="preview-human-name" type="text" value="Alex Demo" required></div><div class="field"><label for="preview-human-phone">Phone</label><input id="preview-human-phone" type="tel" value="7805550100" required></div><div class="field span-2"><label for="preview-human-email">Email</label><input id="preview-human-email" type="email" value="customer@example.com" required></div>`}
      <div class="field span-2"><label for="preview-dog-notes">Dog notes</label><textarea id="preview-dog-notes" maxlength="500" placeholder="Temperament, coat notes or handling preferences."></textarea></div>
      <div class="estimate-box span-2"><span>Estimated ${grooming?'grooming':'daycare'} price</span><strong id="${grooming?'grooming':'daycare'}-estimate"></strong><small>Uses the same rates as the PHP application. Estimate excludes tax and special handling.</small></div>
      <div class="span-2 button-row"><button class="button button-coral" type="button" id="preview-request">Preview request</button><a class="button button-outline" href="demo.html#customer">Watch a real booking</a></div>
      <p class="preview-note span-2">Static demonstration only. Nothing entered here is saved or sent.</p><div id="preview-feedback" class="span-2" role="status" aria-live="polite"></div>
      </form>`;
    confirm.hidden = false;
    const form = confirm.querySelector('form');
    const size = confirm.querySelector(grooming?'#dog_size':'#daycare_dog_size');
    const estimate = confirm.querySelector(grooming?'#grooming-estimate':'#daycare-estimate');
    function updatePrice() {
      const price = grooming ? catalog.services[form.querySelector('input[name="preview_service"]:checked').value].rates[size.value]*groomer.factor : catalog.daycareRates[state.period][size.value];
      estimate.textContent = money(Math.round((price+Number.EPSILON)*100)/100);
      form.querySelectorAll('.service-option').forEach(label=>label.classList.toggle('is-selected',label.querySelector('input').checked));
    }
    form.addEventListener('change',updatePrice);
    form.querySelector('#preview-saved-dog').addEventListener('change',event=>{
      if(event.target.value==='mochi') {
        form.querySelector(grooming?'#dog_name':'#daycare_dog_name').value='Mochi';
        size.value='M';
        form.querySelector('#preview-dog-notes').value='Fictional sample pet. Enjoys a calm introduction.';
        updatePrice();
      }
    });
    form.querySelector('#preview-request').addEventListener('click',()=>{
      if(!form.reportValidity())return;
      form.querySelector('#preview-feedback').innerHTML='<div class="flash flash-success preview-feedback">Preview complete — no booking was saved. <a href="demo.html#customer">See the real submission in the recording →</a></div>';
    });
    updatePrice();
    confirm.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'start'});
  }
  tabs.addEventListener('click',event=>{
    const tab=event.target.closest('[data-service]');if(!tab)return;
    event.preventDefault();state.service=tab.dataset.service;state.groomer=null;state.period=null;render();
  });
  dateStrip.addEventListener('click',event=>{
    const date=event.target.closest('[data-date]');if(!date||date.disabled)return;
    state.date=date.dataset.date;state.groomer=null;state.period=null;render();
  });
  results.addEventListener('click',event=>{
    const slot=event.target.closest('[data-groomer]');
    const period=event.target.closest('[data-period]');
    if(slot){state.groomer=Number(slot.dataset.groomer);state.time=slot.dataset.time;showForm();}
    if(period){state.period=period.dataset.period;showForm();}
  });
  render();
})();
