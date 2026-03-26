ALTER TABLE announcement_popup
ADD COLUMN recurrence_seconds INT DEFAULT NULL,
ADD COLUMN forced_reset_at DATETIME DEFAULT NULL;
