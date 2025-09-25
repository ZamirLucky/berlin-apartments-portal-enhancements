# berlin-apartments-portal-enhancements
Enhancements and new feature implementations for the Berlin Apartments Portal.

## Incomplete: Offline Device Alert Feature (MVP) 

We’re using a cron-driven controller (Nuki_State_Controller::cronPoll) to check Nuki, update the state logs, and send one email when a lock has been offline for 10+ minutes.
After a successful send, we mark that state-logs row with 'alert sent at' so we don’t spam, this keeps it idempotent and not tied to the UI.

Sends **one email** when a smartlock has been **offline ≥ 10 minutes**. (Resolve & reminders to follow.)

### What changed
- **Config:** email alerts configured in `config/config.php`  
  (SMTP credentials, `CRON TOKEN`, email subjects/body).
- **Mailer:** added `Mailer.php` (PHPMailer) under the controller directory.
- **Controller:** added `cronPoll()`, `formatDuration()`, and `getPdo()` to `Nuki_State_Controller.php`.
- **CLI runner:** added `public/cron_nuki.php` (calls `cronPoll()`).
- **DB:** added `alert sent DATETIME NULL` to smartlock state logs table.
### What to make sure
- Need to make sure the code blocks and the overall approach for this feature are solid, and confirm how to run the crontab.



![IMG-20250915-WA0000](https://github.com/user-attachments/assets/1a89e49c-2479-49e7-ac83-8c46b9757b84)
