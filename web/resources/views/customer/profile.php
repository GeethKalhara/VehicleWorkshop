<?php
/**
 * Customer Profile View
 *
 * @var string $csrf
 * @var array<string, string> $assets
 * @var array{
 *     first_name?: string,
 *     last_name?: string,
 *     full_name?: string,
 *     email?: string,
 *     phone?: string,
 *     initials?: string,
 *     badge?: string
 * }|null $profile
 */

$profile = $profile ?? [];
$full_name = isset($profile['full_name']) ? (string) $profile['full_name'] : 'Kasun Perera';
$initials = isset($profile['initials']) ? (string) $profile['initials'] : 'KP';
$email = isset($profile['email']) ? (string) $profile['email'] : 'kasun.perera@example.com';
$phone = isset($profile['phone']) ? (string) $profile['phone'] : '+94 771 123 4567';
$badge = isset($profile['badge']) ? (string) $profile['badge'] : 'Verified';
?>

<div class="profile-page">
    <header class="profile-page-header">
        <div class="profile-page-title-group">
            <h1 class="dashboard-heading">Profile Details</h1>
            <p class="dashboard-subheading">Manage your personal account details, contact information, and security credentials.</p>
        </div>
    </header>

    <section class="profile-card" aria-label="Customer profile form">
        <div class="profile-identity-row">
            <div class="profile-avatar" aria-label="Customer profile initials"><?= htmlspecialchars($initials, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
            <div class="profile-name-block">
                <span class="profile-name"><?= htmlspecialchars($full_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                <span class="profile-status-dot" aria-label="Profile verified"></span>
            </div>
        </div>

        <div class="profile-section">
            <div class="profile-section-heading">
                <h2>Personal Information</h2>
                <p>Primary contact details linked to your service and billing profile.</p>
            </div>

            <div class="profile-form-row">
                <label class="profile-field">
                    <span class="field-label">Full Name</span>
                    <input type="text" value="<?= htmlspecialchars($full_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" aria-label="Full name" />
                </label>
            </div>

            <div class="profile-form-row">
                <label class="profile-field">
                    <span class="field-label">Email Address</span>
                    <div class="field-input-wrap field-with-status">
                        <input type="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" aria-label="Email address" />
                        <span class="field-status-chip"><?= htmlspecialchars($badge, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </div>
                </label>
            </div>

            <div class="profile-form-row">
                <label class="profile-field">
                    <span class="field-label">Mobile Number</span>
                    <div class="field-input-wrap">
                        <span class="field-prefix">☎</span>
                        <input type="tel" value="<?= htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" aria-label="Mobile number" />
                    </div>
                </label>
            </div>
        </div>

        <div class="profile-section">
            <div class="profile-section-heading">
                <h2>Security &amp; Password</h2>
                <p>Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <div class="profile-form-row">
                <label class="profile-field">
                    <span class="field-label">Current Password</span>
                    <div class="field-input-wrap field-with-action">
                        <input type="password" value="••••••••••" aria-label="Current password" />
                        <button type="button" class="field-visibility-btn" aria-label="Toggle current password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </label>
            </div>

            <div class="profile-form-row">
                <label class="profile-field">
                    <span class="field-label">New Password</span>
                    <div class="field-input-wrap field-with-action">
                        <input type="password" placeholder="Enter new password (min. 8 characters)" aria-label="New password" />
                        <button type="button" class="field-visibility-btn" aria-label="Toggle new password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </label>
            </div>
        </div>

        <div class="profile-actions">
            <button type="button" class="btn btn-secondary">Cancel</button>
            <button type="button" class="btn btn-primary">Save Changes</button>
        </div>
    </section>
</div>
