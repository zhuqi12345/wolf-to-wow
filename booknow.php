<?php
require_once __DIR__ . '/layout.php';

$allowedServices = ['grooming', 'daycare'];
$service = in_array($_GET['service'] ?? '', $allowedServices, true) ? $_GET['service'] : 'grooming';
$date = normalize_booking_date($_GET['date'] ?? date('Y-m-d'));
$user = current_user();
$dogs = $user ? get_user_dogs($user['USR_ID']) : [];
$groomers = get_groomers();
$slots = grooming_time_slots();
$periods = daycare_periods();
$sizes = size_labels();

$selectedGroomer = $_GET['groomer_id'] ?? '';
$selectedTime = $_GET['time'] ?? '';
$selectedPeriod = $_GET['period'] ?? '';

render_header('Book Online', 'booking');
?>

<section class="band band-white">
    <div class="band-inner">
        <span class="eyebrow">Book online</span>
        <h1>Choose an available spot first.</h1>
        <p class="text-measure">Bookings can be requested up to 14 days ahead. Grooming availability is by groomer, start time, and weekly capacity. Daycare availability is by period and remaining capacity.</p>

        <div class="booking-tabs" aria-label="Booking service type">
            <a class="<?= $service === 'grooming' ? 'is-active' : '' ?>" href="booknow.php?service=grooming&date=<?= e($date) ?>#booking-availability">Grooming</a>
            <a class="<?= $service === 'daycare' ? 'is-active' : '' ?>" href="booknow.php?service=daycare&date=<?= e($date) ?>#booking-availability">Daycare</a>
        </div>

        <div class="date-strip" aria-label="Booking date selection">
            <?php foreach (next_booking_dates() as $candidate): ?>
                <?php
                $disabled = is_weekend($candidate);
                $classes = ['date-pill'];
                if ($candidate === $date) {
                    $classes[] = 'is-active';
                }
                if ($disabled) {
                    $classes[] = 'is-disabled';
                }
                ?>
                <a class="<?= e(implode(' ', $classes)) ?>" href="booknow.php?service=<?= e($service) ?>&date=<?= e($candidate) ?>#booking-availability">
                    <strong><?= e(date('M j', strtotime($candidate))) ?></strong>
                    <span><?= e(date('D', strtotime($candidate))) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!valid_booking_date($date)): ?>
            <div class="booking-panel" id="booking-availability">
                <h2>Closed for this date.</h2>
                <p>Please choose a weekday in the two-week booking window.</p>
            </div>
        <?php elseif ($service === 'grooming'): ?>
            <div class="booking-panel" id="booking-availability">
                <h2>Grooming availability for <?= e(pretty_date($date)) ?></h2>
                <table class="availability-table">
                    <thead>
                        <tr>
                            <th>Start time</th>
                            <?php foreach ($groomers as $groomer): ?>
                                <th title="<?= e(groomer_rate_note($groomer)) ?>"><?= e($groomer['GRM_GroomerName']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $timeValue => $timeLabel): ?>
                            <tr>
                                <th><?= e($timeLabel) ?></th>
                                <?php foreach ($groomers as $groomer): ?>
                                    <td data-label="<?= e($groomer['GRM_GroomerName']) ?>">
                                        <?php if (grooming_slot_available($date, $timeValue, $groomer['GRM_ID'])): ?>
                                            <?php
                                            $selectUrl = 'booknow.php?service=grooming&date=' . urlencode($date)
                                                . '&groomer_id=' . urlencode($groomer['GRM_ID'])
                                                . '&time=' . urlencode($timeValue)
                                                . '#confirm-booking';
                                            ?>
                                            <?php if ($user): ?>
                                                <a class="slot-button" href="<?= e($selectUrl) ?>">Available</a>
                                            <?php else: ?>
                                                <a class="slot-button" href="login.php?return=<?= e(urlencode($selectUrl)) ?>">Log in or register</a>
                                            <?php endif; ?>
                                        <?php elseif (grooming_slot_taken($date, $timeValue, $groomer['GRM_ID'])): ?>
                                            <span class="slot-unavailable">Booked</span>
                                        <?php else: ?>
                                            <span class="slot-unavailable">Weekly limit</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="fine-print">Hover a groomer name to see a rough average. Final estimates are calculated after service and size selection.</p>
            </div>

            <?php if ($user && $selectedGroomer && $selectedTime && grooming_slot_available($date, $selectedTime, $selectedGroomer)): ?>
                <?php $groomer = groomer_by_id($selectedGroomer); ?>
                <section class="form-surface booking-confirm" id="confirm-booking">
                    <h2>Confirm grooming request</h2>
                    <p><?= e($groomer['GRM_GroomerName'] ?? 'Selected groomer') ?> at <?= e($slots[$selectedTime] ?? $selectedTime) ?> on <?= e(pretty_date($date)) ?>.</p>
                    <form action="booknow_processgrooming.php" method="post" class="form-grid" id="grooming-form">
                        <input type="hidden" name="appt_date" value="<?= e($date) ?>">
                        <input type="hidden" name="appt_time" value="<?= e($selectedTime) ?>">
                        <input type="hidden" name="grm_id" value="<?= e($selectedGroomer) ?>">
                        <input type="hidden" id="groomer_multiplier" value="<?= e(groomer_multiplier($selectedGroomer)) ?>">

                        <?php if ($dogs): ?>
                            <div class="field span-2">
                                <label for="saved_dog">Use saved dog details</label>
                                <select id="saved_dog">
                                    <option value="">Manual entry</option>
                                    <?php foreach ($dogs as $dog): ?>
                                        <option value="<?= e($dog['DOG_ID']) ?>" data-name="<?= e($dog['DOG_Name']) ?>" data-size="<?= e($dog['DOG_Size']) ?>" data-notes="<?= e($dog['DOG_Notes']) ?>"><?= e($dog['DOG_Name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="field">
                            <label for="dog_name">Dog name</label>
                            <input type="text" id="dog_name" name="dog_name" maxlength="50" required>
                        </div>
                        <div class="field">
                            <label for="dog_size">Dog size</label>
                            <select id="dog_size" name="dog_size" required>
                                <?php foreach ($sizes as $sizeKey => $label): ?>
                                    <option value="<?= e($sizeKey) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field span-2">
                            <label>Service</label>
                            <div class="service-option-grid">
                                <?php foreach (grooming_services() as $key => $option): ?>
                                    <label class="service-option <?= $key === 'bath_brush' ? 'is-selected' : '' ?>">
                                        <input type="radio" name="service" value="<?= e($key) ?>" data-rates="<?= e(json_encode($option['rates'])) ?>" <?= $key === 'bath_brush' ? 'checked' : '' ?> required>
                                        <span class="service-option-copy">
                                            <strong><?= e($option['label']) ?></strong>
                                            <small>From <?= e(money((float) min($option['rates']))) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="field span-2">
                            <label for="dog_notes">Dog notes</label>
                            <textarea id="dog_notes" name="dog_notes" maxlength="500" placeholder="Temperament, coat notes, allergies, handling notes, or anything staff should know."></textarea>
                        </div>
                        <div class="estimate-box span-2">
                            <span>Estimated grooming price</span>
                            <strong id="grooming-estimate">$0.00</strong>
                            <small>Estimate only. Staff will confirm if coat condition or special handling changes the final visit.</small>
                        </div>
                        <div class="span-2">
                            <button class="button button-coral" type="submit">Request Grooming Booking</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

        <?php else: ?>
            <div class="daycare-grid" id="booking-availability">
                <?php foreach ($periods as $periodKey => $period): ?>
                    <?php
                    $left = daycare_spots_left($date, $periodKey);
                    $selectUrl = 'booknow.php?service=daycare&date=' . urlencode($date) . '&period=' . urlencode($periodKey) . '#confirm-booking';
                    ?>
                    <article class="period-card">
                        <span class="spots <?= $left <= 3 ? 'low' : '' ?>"><?= e($left) ?> spots left</span>
                        <h3><?= e($period['label']) ?></h3>
                        <p><?= e($period['time']) ?></p>
                        <?php if ($left > 0): ?>
                            <?php if ($user): ?>
                                <a class="button button-outline" href="<?= e($selectUrl) ?>">Choose This Period</a>
                            <?php else: ?>
                                <a class="button button-outline" href="login.php?return=<?= e(urlencode($selectUrl)) ?>">Log in or register</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="slot-unavailable">Full</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($user && isset($periods[$selectedPeriod]) && daycare_spots_left($date, $selectedPeriod) > 0): ?>
                <section class="form-surface booking-confirm" id="confirm-booking">
                    <h2>Confirm daycare request</h2>
                    <p><?= e($periods[$selectedPeriod]['label']) ?> on <?= e(pretty_date($date)) ?>, <?= e($periods[$selectedPeriod]['time']) ?>.</p>
                    <form action="booknow_processdaycare.php" method="post" class="form-grid" id="daycare-form">
                        <input type="hidden" name="daybk_date" value="<?= e($date) ?>">
                        <input type="hidden" name="daybk_period" value="<?= e($selectedPeriod) ?>">

                        <?php if ($dogs): ?>
                            <div class="field span-2">
                                <label for="daycare_saved_dog">Use saved dog details</label>
                                <select id="daycare_saved_dog">
                                    <option value="">Manual entry</option>
                                    <?php foreach ($dogs as $dog): ?>
                                        <option value="<?= e($dog['DOG_ID']) ?>" data-name="<?= e($dog['DOG_Name']) ?>" data-size="<?= e($dog['DOG_Size']) ?>" data-notes="<?= e($dog['DOG_Notes']) ?>"><?= e($dog['DOG_Name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="field">
                            <label for="daycare_dog_name">Dog name</label>
                            <input type="text" id="daycare_dog_name" name="daybk_dog_name" maxlength="50" required>
                        </div>
                        <div class="field">
                            <label for="daycare_dog_size">Dog size</label>
                            <select id="daycare_dog_size" name="daybk_dog_size" required>
                                <?php foreach ($sizes as $sizeKey => $label): ?>
                                    <option value="<?= e($sizeKey) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="human_name">Your name</label>
                            <input type="text" id="human_name" name="daybk_human_name" maxlength="100" value="<?= e($user['USR_FirstName'] . ' ' . $user['USR_LastName']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="human_phone">Phone</label>
                            <input type="tel" id="human_phone" name="daybk_human_phone" maxlength="20" value="<?= e($user['USR_Phone']) ?>" required>
                        </div>
                        <div class="field span-2">
                            <label for="human_email">Email</label>
                            <input type="email" id="human_email" name="daybk_human_email" maxlength="100" value="<?= e($user['USR_Email']) ?>" required>
                        </div>
                        <div class="field span-2">
                            <label for="daycare_dog_notes">Dog notes</label>
                            <textarea id="daycare_dog_notes" name="daybk_dog_notes" maxlength="500"></textarea>
                        </div>
                        <div class="estimate-box span-2">
                            <span>Estimated daycare price</span>
                            <strong id="daycare-estimate"><?= e(money(estimate_daycare_price($selectedPeriod, 'S'))) ?></strong>
                            <small>Estimate only. Payment is handled after staff confirmation.</small>
                        </div>
                        <div class="span-2">
                            <button class="button button-coral" type="submit">Request Daycare Booking</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script>
const money = (value) => '$' + Number(value || 0).toFixed(2);

function fillDog(selectId, nameId, sizeId, notesId) {
  const select = document.getElementById(selectId);
  if (!select) return;
  select.addEventListener('change', () => {
    const option = select.selectedOptions[0];
    if (!option || !option.dataset.name) return;
    document.getElementById(nameId).value = option.dataset.name || '';
    document.getElementById(sizeId).value = option.dataset.size || 'S';
    document.getElementById(notesId).value = option.dataset.notes || '';
    document.getElementById(sizeId).dispatchEvent(new Event('change'));
  });
}

function updateGroomingEstimate() {
  const form = document.getElementById('grooming-form');
  if (!form) return;
  const size = document.getElementById('dog_size').value;
  const multiplier = Number(document.getElementById('groomer_multiplier').value || 1);
  let total = 0;
  const selected = form.querySelector('input[name="service"]:checked');
  const rates = JSON.parse(selected?.dataset.rates || '{}');
  total = Number(rates[size] || 0);
  document.getElementById('grooming-estimate').textContent = money(total * multiplier);
  form.querySelectorAll('.service-option').forEach((option) => {
    option.classList.toggle('is-selected', Boolean(option.querySelector('input[name="service"]')?.checked));
  });
}

function updateDaycareEstimate() {
  const select = document.getElementById('daycare_dog_size');
  const target = document.getElementById('daycare-estimate');
  if (!select || !target) return;
  const rates = <?= json_encode(daycare_rates()[$selectedPeriod] ?? []) ?>;
  target.textContent = money(rates[select.value] || 0);
}

fillDog('saved_dog', 'dog_name', 'dog_size', 'dog_notes');
fillDog('daycare_saved_dog', 'daycare_dog_name', 'daycare_dog_size', 'daycare_dog_notes');
document.querySelectorAll('#grooming-form input, #grooming-form select').forEach((input) => input.addEventListener('change', updateGroomingEstimate));
document.getElementById('daycare_dog_size')?.addEventListener('change', updateDaycareEstimate);
updateGroomingEstimate();
updateDaycareEstimate();
</script>

<?php render_footer(); ?>
