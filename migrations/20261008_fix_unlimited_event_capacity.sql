-- Empty capacity fields were previously stored as zero on non-strict MySQL configurations.
-- Zero is not a valid configured limit; normalize it to the schema's unlimited value (NULL).
UPDATE `events`
SET `capacity` = NULL
WHERE `capacity` = 0;

-- Waitlisted RSVPs on unlimited events were created only because zero was treated as full.
UPDATE `event_rsvps` AS r
INNER JOIN `events` AS e ON e.`id` = r.`event_id`
SET r.`status` = 'booked'
WHERE r.`status` = 'waitlisted'
  AND e.`capacity` IS NULL
  AND e.`status` = 'scheduled';
