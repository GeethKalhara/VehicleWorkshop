<?php
/**
 * Customer Appointment Booking View
 *
 * @var array<string, string> $assets
 * @var list<array{
 *     id: int,
 *     license_plate: string,
 *     model: string,
 *     trim_badge: string,
 *     image: string,
 *     color_name: string,
 *     color_hex: string,
 *     status: 'idle'
 * }> $vehicles
 */

$escape = static fn (string|int|float|null $value): string => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
?>

<div class="booking-page">
    <header class="booking-page-header">
        <div>
            <h1 class="dashboard-heading">Book an Appointment</h1>
            <p class="dashboard-subheading">Choose a vehicle and a date, then tell us what service you need.</p>
        </div>
        <a class="booking-back-link" href="/customer/appointments">Back to Appointments</a>
    </header>

    <section class="booking-card" aria-labelledby="booking-form-title">
        <div class="booking-card-heading">
            <div class="booking-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v4m8-4v4M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1z" />
                </svg>
            </div>
            <div>
                <h2 id="booking-form-title">New Appointment</h2>
                <p>Select a vehicle and your preferred appointment date.</p>
            </div>
        </div>

        <?php if ($vehicles === []): ?>
            <div class="booking-empty-state" role="status">
                <h3>No vehicles are currently available to book</h3>
                <p>Vehicles already in service cannot be selected. Please try again when a vehicle is available.</p>
                <a href="/customer/vehicles">View My Vehicles</a>
            </div>
        <?php else: ?>
            <form class="booking-form" id="appointment-booking-form">
                <div class="booking-calendar-column">
                    <label class="booking-field-label" for="booking-vehicle">Select Vehicle</label>
                    <select class="booking-vehicle-select" id="booking-vehicle" name="vehicle_id" required>
                        <option value="" selected disabled>Choose one of your available vehicles</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= $escape($vehicle['id']) ?>">
                                <?= $escape($vehicle['model']) ?> · <?= $escape($vehicle['license_plate']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="booking-calendar-heading">
                        <div>
                            <h3>Select an Appointment Date</h3>
                            <p>Choose any available future date.</p>
                        </div>
                        <div class="booking-calendar-nav">
                            <button type="button" class="calendar-nav-button" data-calendar-prev aria-label="Previous month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                                </svg>
                            </button>
                            <button type="button" class="calendar-nav-button" data-calendar-next aria-label="Next month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="booking-calendar" aria-label="Appointment date calendar">
                        <div class="calendar-month-label" aria-live="polite"></div>
                        <div class="calendar-weekdays" aria-hidden="true">
                            <span>Mon</span>
                            <span>Tue</span>
                            <span>Wed</span>
                            <span>Thu</span>
                            <span>Fri</span>
                            <span>Sat</span>
                            <span>Sun</span>
                        </div>
                        <div class="booking-calendar-grid" role="group" aria-label="Choose appointment date"></div>
                    </div>
                    <input type="hidden" name="appointment_date" id="appointment-date" required>
                    <p class="booking-selected-date" id="booking-selected-date" aria-live="polite">No date selected</p>
                </div>

                <div class="booking-description-column">
                    <label class="booking-field-label" for="booking-description">Description &amp; Specific Concerns</label>
                    <p class="booking-field-help">Tell us about any issues or services you need.</p>
                    <textarea
                        id="booking-description"
                        name="description"
                        rows="6"
                        maxlength="2000"
                        placeholder="Describe any specific issues (e.g. brake vibration, AC filter replacement)..."
                        required
                    ></textarea>

                    <div class="booking-submit-area">
                        <p class="booking-submit-note">Review the selected vehicle, date, and service concerns.</p>
                        <button class="btn-book-service" type="submit" id="booking-submit-button">
                            <span>Review Booking</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7" />
                            </svg>
                        </button>
                        <p class="booking-feedback" id="booking-feedback" role="status" aria-live="polite"></p>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<dialog class="booking-review-dialog" id="booking-review-dialog" aria-labelledby="booking-review-title">
    <div class="booking-review-heading">
        <div>
            <span class="booking-review-eyebrow">Review Booking</span>
            <h2 id="booking-review-title">Confirm appointment details</h2>
        </div>
        <button type="button" class="booking-review-close" id="booking-review-close" aria-label="Close booking summary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <dl class="booking-review-summary">
        <div>
            <dt>Vehicle</dt>
            <dd id="booking-review-vehicle"></dd>
        </div>
        <div>
            <dt>Appointment date</dt>
            <dd id="booking-review-date"></dd>
        </div>
        <div>
            <dt>Service concerns</dt>
            <dd id="booking-review-description"></dd>
        </div>
    </dl>

    <div class="booking-review-actions">
        <button type="button" class="booking-review-edit" id="booking-review-edit">Go Back</button>
        <button type="button" class="booking-review-confirm" id="booking-review-confirm">
            Confirm Booking
        </button>
    </div>

    <div class="booking-confirming-state" id="booking-confirming-state" role="status" aria-live="polite" hidden>
        <span class="booking-confirming-spinner" aria-hidden="true"></span>
        <p>Confirming your booking...</p>

    </div>
</dialog>
