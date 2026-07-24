-- Expand legacy full-day restriction blockers (single 08:00 row) to all operating slots.
INSERT INTO reservation (count, telephone, name, has_children, email, user_id, date, comment)
SELECT 5, 'restriction', 'restriction', 0, NULL, NULL, DATE_ADD(DATE(r.date), INTERVAL h.hour HOUR), NULL
FROM reservation r
CROSS JOIN (
    SELECT 9 AS hour
    UNION ALL SELECT 10
    UNION ALL SELECT 11
    UNION ALL SELECT 13
    UNION ALL SELECT 14
    UNION ALL SELECT 15
    UNION ALL SELECT 16
    UNION ALL SELECT 17
) h
WHERE r.name = 'restriction'
  AND TIME(r.date) = '08:00:00'
  AND NOT EXISTS (
      SELECT 1
      FROM reservation x
      WHERE x.name = 'restriction'
        AND x.date = DATE_ADD(DATE(r.date), INTERVAL h.hour HOUR)
  );
