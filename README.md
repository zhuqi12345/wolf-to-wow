# Wolf to Wow

**2026 Skills Alberta Web Design Competition Project**

An Edmonton dog grooming and daycare website with responsive pages, service
estimates, customer booking and a staff approval dashboard.

**[Frontend demo](https://zhuqi12345.github.io/wolf-to-wow/)** ·
**[Customer booking video](https://zhuqi12345.github.io/wolf-to-wow/demo.html#customer)** ·
**[Staff approval video](https://zhuqi12345.github.io/wolf-to-wow/demo.html#staff)**

[![Desktop frontend preview](docs/media/home-desktop.jpg)](https://zhuqi12345.github.io/wolf-to-wow/)

## Project background

This project was developed for the **2026 Skills Alberta Web Design competition**.
The brief was to improve a dog grooming and daycare business website using
supplied starter code, content and images. The work covers the visual redesign,
responsive layouts, booking estimates, database-backed requests and staff review.

## Explore the demo

| Demo | What it shows |
| --- | --- |
| [Frontend website](https://zhuqi12345.github.io/wolf-to-wow/) | Responsive pages, navigation, service selection and interactive price estimates |
| [Customer booking · 36 seconds](https://zhuqi12345.github.io/wolf-to-wow/demo.html#customer) | Choosing a grooming appointment, calculating a price and saving a request |
| [Staff approval · 11 seconds](https://zhuqi12345.github.io/wolf-to-wow/demo.html#staff) | Reviewing a pending request and saving an administrator action |

The website is a static frontend preview. The videos show the real PHP/MySQL
application running locally with fictional customer data. Login, submissions and
database updates are demonstrated in the recordings.

<details>
<summary>Mobile frontend preview</summary>

<img src="docs/media/home-mobile.jpg" alt="Mobile homepage and navigation" width="300">

</details>

## Backend highlights

- **Booking rules:** a 14-day booking window, weekday checks, groomer time slots,
  weekly work-hour limits and shared morning/afternoon daycare capacity.
- **Price calculation:** the server calculates grooming estimates from service,
  dog size and groomer rate; daycare uses the selected period and dog size.
- **Booking lifecycle:** requests start pending; staff can confirm or cancel them.
  Customers can cancel their own upcoming bookings, releasing capacity.
- **Accounts:** password hashing, session-based login, owner-specific records and
  a staff permission check.

<details>
<summary>Full backend workflow diagram</summary>

![Backend workflow](docs/diagrams/backend-flow.svg)

[Open full-size diagram](https://zhuqi12345.github.io/wolf-to-wow/diagrams/backend-flow.svg)

</details>

Source: [booking rules](app.php) · [grooming submission](booknow_processgrooming.php) ·
[daycare submission](booknow_processdaycare.php) · [staff review](staff.php) ·
[cancellation](cancel_booking.php) · [customer accounts](account.php).

**Stack:** HTML, CSS, JavaScript, PHP 8.2, mysqli and MariaDB 10.4.

<details>
<summary>Run the full PHP application locally</summary>

1. Place the project in `C:\xampp\htdocs\wolf-to-wow` and start Apache and MySQL.
2. Copy `config.local.example.php` to `config.local.php` if needed. Set database
   credentials and keep the database name `wolf_to_wow_demo`.
3. From the project folder, run:

   ```powershell
   C:\xampp\php\php.exe scripts/init-demo.php --demo
   ```

4. Open `http://localhost/wolf-to-wow/` in your browser.

Demo logins: `customer@example.com` and `staff@example.com`; initial password for
both: `PortfolioDemo123!`. Email requires a working mail service.

</details>

Based on the supplied competition starter materials. Portfolio preparation used
AI assistance.
