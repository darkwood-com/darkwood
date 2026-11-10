#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;

if (@ARGV != 1) { die "Usage : td2_exo5.pl <REP>\n"; }

my $rep = $ARGV[0]; $rep =~s/\/$//;
my ($nbDocs, $ligne, $terme, $nbOccs);

open ENTREE, "ls lemonde/*.lemmes | wc -l |";
$nbDocs = <ENTREE>;

open(FILE,">corpus_docFreq.txt");
print FILE "N=$nbDocs";

open TERMES_DOCS, "cat $rep/*.lemmes | sort | cut -f1 | uniq -c |";

while ($ligne = <TERMES_DOCS>) {
	if ($ligne =~ /(\d+)\s(.+)$/ ) { print FILE "$2\t$1\n"; }
}
close FILE; close TERMES_DOCS;