# Paste into the Hostinger VPS web terminal (root). Step 1 only shows info, step 2 makes changes.

# ---------- STEP 1: find the site + database (read-only) ----------
SITE=$(grep -ril "tanveerbrother" /etc/nginx/sites-enabled/ 2>/dev/null | head -1 | xargs grep -m1 -oP 'root\s+\K[^;]+' | sed 's#/public$##')
echo "SITE=$SITE"
ENVV() { grep -m1 "^$1=" "$SITE/.env" | cut -d= -f2- | tr -d '"'"'"; }
DB=$(ENVV DB_DATABASE); DBU=$(ENVV DB_USERNAME); DBP=$(ENVV DB_PASSWORD)
echo "DB=$DB USER=$DBU"
mysql -u"$DBU" -p"$DBP" "$DB" -e "SELECT id,party_id,amount,date,created_at FROM payments WHERE date > CURDATE() OR date < '2020-01-01'; SELECT id,amount,date,created_at FROM expenses WHERE date > CURDATE() OR date < '2020-01-01';"

# ---------- STEP 2: backup, then fix dates ----------
mysqldump --single-transaction -u"$DBU" -p"$DBP" "$DB" > /root/backup_${DB}_$(date +%F_%H%M).sql && echo "BACKUP OK: $(ls -t /root/backup_${DB}_* | head -1)" && \
mysql -u"$DBU" -p"$DBP" "$DB" <<'SQL'
START TRANSACTION;
UPDATE payments       SET date='2026-04-11' WHERE id IN (1497,1499,1500,1501,1502,1503,1504,1505,1506,1507,1508,1509) AND date='2026-11-04';
UPDATE ledger_entries SET date='2026-04-11' WHERE reference_type='payment' AND reference_id IN (1497,1499,1500,1501,1502,1503,1504,1505,1506,1507,1508,1509) AND date='2026-11-04';
UPDATE expenses       SET date='2026-04-11' WHERE id IN (61,63,64,65,66,68) AND date='2026-11-04';
UPDATE ledger_entries SET date='2026-04-11' WHERE reference_type='expense' AND reference_id IN (61,63,64,65,66,68) AND date='2026-11-04';
UPDATE payments       SET date='2026-02-16' WHERE id=212 AND date='0005-02-16';
UPDATE ledger_entries SET date='2026-02-16' WHERE reference_type='payment' AND reference_id=212 AND date='0005-02-16';
UPDATE payments       SET date='2026-03-15' WHERE id IN (820,821,824,825) AND date='0005-03-15';
UPDATE ledger_entries SET date='2026-03-15' WHERE reference_type='payment' AND reference_id IN (820,821,824,825) AND date='0005-03-15';
UPDATE payments       SET date='2026-03-16' WHERE id=845 AND date='0006-03-16';
UPDATE ledger_entries SET date='2026-03-16' WHERE reference_type='payment' AND reference_id=845 AND date='0006-03-16';
UPDATE payments       SET date='2026-04-27' WHERE id=1938 AND date='0004-04-27';
UPDATE ledger_entries SET date='2026-04-27' WHERE reference_type='payment' AND reference_id=1938 AND date='0004-04-27';
UPDATE payments       SET date='2026-07-09' WHERE id=4059 AND date='0009-07-09';
UPDATE ledger_entries SET date='2026-07-09' WHERE reference_type='payment' AND reference_id=4059 AND date='0009-07-09';
COMMIT;
SELECT p.opening_balance + SUM(l.debit) - SUM(l.credit) AS farhan_current_balance
FROM parties p JOIN ledger_entries l ON l.party_id = p.id WHERE p.id = 131;
SQL
