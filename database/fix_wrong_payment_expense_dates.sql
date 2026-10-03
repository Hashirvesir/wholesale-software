-- Fix payments/expenses saved with a wrong date (day/month swapped or wrong year).
-- Party "current balance" sums ALL ledger entries, while the Party Ledger report only
-- sums entries up to the selected end date, so a future-dated entry made the two differ.
-- Correct date is inferred from created_at (entries were recorded 1-2 days after the date).
-- Updates both the document table and its ledger_entries rows.

START TRANSACTION;

-- 2026-11-04 entered instead of 2026-04-11 (created 2026-04-13)
UPDATE payments       SET date = '2026-04-11' WHERE id IN (1497,1499,1500,1501,1502,1503,1504,1505,1506,1507,1508,1509) AND date = '2026-11-04';
UPDATE ledger_entries SET date = '2026-04-11' WHERE reference_type = 'payment' AND reference_id IN (1497,1499,1500,1501,1502,1503,1504,1505,1506,1507,1508,1509) AND date = '2026-11-04';

UPDATE expenses       SET date = '2026-04-11' WHERE id IN (61,63,64,65,66,68) AND date = '2026-11-04';
UPDATE ledger_entries SET date = '2026-04-11' WHERE reference_type = 'expense' AND reference_id IN (61,63,64,65,66,68) AND date = '2026-11-04';

-- Year typed wrong (e.g. 0005 instead of 2026)
UPDATE payments       SET date = '2026-02-16' WHERE id = 212 AND date = '0005-02-16';
UPDATE ledger_entries SET date = '2026-02-16' WHERE reference_type = 'payment' AND reference_id = 212 AND date = '0005-02-16';

UPDATE payments       SET date = '2026-03-15' WHERE id IN (820,821,824,825) AND date = '0005-03-15';
UPDATE ledger_entries SET date = '2026-03-15' WHERE reference_type = 'payment' AND reference_id IN (820,821,824,825) AND date = '0005-03-15';

UPDATE payments       SET date = '2026-03-16' WHERE id = 845 AND date = '0006-03-16';
UPDATE ledger_entries SET date = '2026-03-16' WHERE reference_type = 'payment' AND reference_id = 845 AND date = '0006-03-16';

UPDATE payments       SET date = '2026-04-27' WHERE id = 1938 AND date = '0004-04-27';
UPDATE ledger_entries SET date = '2026-04-27' WHERE reference_type = 'payment' AND reference_id = 1938 AND date = '0004-04-27';

UPDATE payments       SET date = '2026-07-09' WHERE id = 4059 AND date = '0009-07-09';
UPDATE ledger_entries SET date = '2026-07-09' WHERE reference_type = 'payment' AND reference_id = 4059 AND date = '0009-07-09';

COMMIT;
