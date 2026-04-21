# Changelog

## [1.1.10] - 2026-04-20 - Cap Web Solutions

### Added

- Added admin tooling for self-referral review:
	- Show Self-referrals toggle on the All Referrals admin page.
	- Self-referrals count badge near the page heading.
	- Row action and bulk action to send notice email.
	- Email template filter hooks:
		- rpp_self_referral_notice_email_subject
		- rpp_self_referral_notice_email_message
		- rpp_self_referral_notice_email_headers

### Changed

- Hardened notice email handling with improved recipient resolution and failure logging for mail troubleshooting.
- Added nonce and capability checks to CSV download and referral notice actions.
- Updated admin UI copy for clearer action labels and status notices.
- Removed debug noise and cleaned up legacy commented code.

## [1.1.9] - 2026-04-09 - Cap Web Solutions

### Added

- Refined the email message that an admin triggers to notify users of self-referral. 
	- Added more detail to the content of the message. 
	- Specified send as html. 

## [1.1.8] - 2026-03-31 - Cap Web Solutions

### Added

- UI update to add a Show Self referrals on the Referral page, includes:
	- Added Show Self-referrals button next to Download CSV.
	- Added Show All Referrals button when in filtered view.
	- Added admin notice rendering for send-email success/error counts.
	- Wrapped list table in a proper <form method="post"> so bulk actions work.
	- Added bulk-referrals nonce field for bulk actions.
	- Added send_notice_email_for_referral() 
	- Functions to filter notice content
		- rpp_self_referral_notice_email_subject
		- rpp_self_referral_notice_email_message
		- rpp_self_referral_notice_email_headers
	- Self referrals count badge on page
	- Default email notice references user Referral page for ease of access. 

## [1.1.7] - 2026-01-12 - Cap Web Solutions

### Changed

- Corrected issues with new table creation.

## [1.1.6] - 2025-11-06 - Cap Web Solutions

### Changed

- Readded code that had been removed during linting process.
- Some linting rolled back during recovery.

## [1.1.5] - 2025-11-04 - Cap Web Solutions

### Changed

- Applied WP Code Standards and performed linting.

## [1.1.4] - 2025-11-04 - Cap Web Solutions & Copilot AI

### Added

- Ability to select a sub-type of referral if referral type = Networking.
- Added new field to referral table for referral subtype. referral_subtype

### Changed

- Moved Referral Type selector from bottom of form to top.
- Renamed Type Of Referral column in wp_referral_data table to referral_type for consistenacy with primary referral type.

## [1.1.3] - 2025-11-04 - Cap Web Solutions & Copilot AI

### Added

- Ability to select a sub-type of referral if referral type = Networking.
- Added new field to referral table for referral subtype. referral_subtype

### Changed

- Moved Referral Type selector from bottom of form to top.
- Renamed Type Of Referral column in wp_referral_data table to referral_type for consistenacy with primary referral type.

## [1.1.2] - 2025-04-09

### Removed

- Removed the definition of _S_VERSION constant as it was conflicting with the definition in the child theme.

## [1.1.1] - 2024-09-18

### Added

- Added integration with Git Updater
- This CHANGELOG.
