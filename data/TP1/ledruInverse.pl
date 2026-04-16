#!/usr/bin/perl -w

$rep = @ARGV[0];

opendir(DIR, $rep) || die "Impossible d'ouvrir le repertoire $rep\n";
my @files = grep { /^[^.]/ } readdir(DIR);


$treetager = "/home/ensgnt/max/tal/tree-tagger/cmd/tree-tagger-french";
$filtre = "./filtre3.pl";
$options = "sort";

%mots = ();

foreach $element (@files) {
	open ENTREE, "cat $rep/$element | $treetager | $filtre | $options |" || die "Erreur";
	while($ligne = <ENTREE>) {
		$mot = substr($ligne, 0, length($ligne)-1);
		#print "$mot\t$element\n";
		$item = "$mots{$mot}" . "; " . "$element";
		push(@mots, $mot, $item);
	}
	print "@mots";
}



close DIR;