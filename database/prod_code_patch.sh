# Paste the whole file into the Hostinger VPS web terminal (root).
# Adds the "no future / no pre-2020 date" validation to payments & expenses on the live site.
# Live files were verified identical to local before these edits; the script refuses to write
# if a pattern isn't found the expected number of times.

SITE=/home/invenco-tanveerbrother/htdocs/tanveerbrother.invenco.pk
FILES="app/Http/Controllers/PaymentController.php app/Http/Controllers/ExpenseController.php resources/views/payments/create.blade.php resources/views/expenses/create.blade.php resources/views/expenses/edit.blade.php"
BK=/root/code_backup_datefix_$(date +%F_%H%M)
cd "$SITE" && mkdir -p "$BK" && cp -p --parents $FILES "$BK/" && echo "CODE BACKUP OK: $BK"

cat > /root/datefix.pl <<'PERL'
use strict; use warnings;
my $base = $ARGV[0] // '/home/invenco-tanveerbrother/htdocs/tanveerbrother.invenco.pk';

sub patch {
    my ($f, $old, $new, $want) = @_;
    my $p = "$base/$f";
    open my $in, '<:raw', $p or die "$p: $!";
    my $t = do { local $/; <$in> }; close $in;
    my $crlf = $t =~ /\r\n/;
    $t =~ s/\r\n/\n/g;
    my ($n, $pos) = (0, 0);
    while (($pos = index($t, $old, $pos)) >= 0) { $n++; $pos += length $old; }
    die "$f: found $n, expected $want - nothing written\n" unless $n == $want;
    $t =~ s/\Q$old\E/$new/g;
    $t =~ s/\n/\r\n/g if $crlf;
    open my $out, '>:raw', $p or die "$p: $!"; print $out $t; close $out;
    print "patched $f ($n)\n";
}

my $range = q{ min="2020-01-01" max="{{ date('Y-m-d') }}" required />};

patch('app/Http/Controllers/PaymentController.php',
"            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);",
"            // Block typos like 2026-11-04 (for 11-04) or year 0005: a future-dated entry is
            // counted in the party's current balance but not in the ledger report up to today.
            'date' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'note' => 'nullable|string',
        ], [
            'date.before_or_equal' => 'Payment date cannot be in the future.',
            'date.after_or_equal' => 'Please enter a valid payment date.',
        ]);", 1);

patch('app/Http/Controllers/ExpenseController.php',
"            'date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);",
"            'date' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'note' => 'nullable|string|max:1000',
        ], [
            'date.before_or_equal' => 'Expense date cannot be in the future.',
            'date.after_or_equal' => 'Please enter a valid expense date.',
        ]);", 2);

patch('resources/views/payments/create.blade.php',
q{type="date" name="date" x-model="date" required />},
q{type="date" name="date" x-model="date"} . $range, 1);

patch('resources/views/expenses/create.blade.php',
q{:value="old('date', date('Y-m-d'))" required />},
q{:value="old('date', date('Y-m-d'))"} . $range, 1);

patch('resources/views/expenses/edit.blade.php',
q{:value="old('date', $expense->date)" required />},
q{:value="old('date', $expense->date)"} . $range, 1);
PERL

perl /root/datefix.pl && sudo -u invenco-tanveerbrother php artisan view:clear

# Verify - expected:
# 023a9dfe30bd571f842574f15e8eefcb  PaymentController.php
# e5b5ef5457205cd3b2e33d5ba13200cf  ExpenseController.php
# 8133c2a7d3e3059a7561e8ff38a11f24  payments/create.blade.php
# 0fbe38538a623f363e69cf5162c42377  expenses/create.blade.php
# c7eb1ff1660fec2489d3d8bcbec199eb  expenses/edit.blade.php
for f in $FILES; do echo "$(tr -d '\r' < $f | md5sum | cut -c1-32) $f"; done; cd ~

# Undo if needed:  cp -p -r "$BK"/. "$SITE"/
