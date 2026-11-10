#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;

if (@ARGV != 2) { die "Usage : td2_exo6.pl <REP> <FREQ>\n"; }

my $rep = $ARGV[0]; $rep =~s/\/$//;
my $freq = $ARGV[1];

open DOC_CORPUS, $freq;
$nbDocs = <DOC_CORPUS>;

%motsDocs = ();
while($ligne = <DOC_CORPUS>) {
	if ( $ligne =~ /^([^\t]*)\t([^\t]*)$/ ) {
		push @motsDocs, $1, $2;
	}
}

open DOSSIER, "ls lemonde/*.lemmes |";
while($fichier = <DOSSIER>) {
	open FICHIER, $fichier;
	$fwrite = $fichier;
	$fwrite =~s/lemmes$/poids/;
	#open WRITE, ">lemonde/$fwrite."
	print $fwrite;

	while($ligne = <FICHIER>) {
		if ( $ligne =~ /^([^\t]*)\t([^\t]*)$/ ) {
			$mot = $1; chomp $mot;
			$occurr = $2; chomp $occurr;
			
			$poids = $occurr * $nbDocs * $motsDocs{$mot};
			
			print WRITE "$mot $poids";
		}
	}

	close FICHIER;
}

close DOSSIER;