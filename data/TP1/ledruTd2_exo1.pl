#!/usr/bin/perl -w

$doc = $ARGV[0];

$treetager = "/home/ensgnt/max/tal/tree-tagger/cmd/tree-tagger-french";
$filtre = "./filtre4.pl";
$options = "sort -f";

open ENTREE, "cat $doc | $treetager | $filtre | $options |" || die "Erreur";
$mot = "";
$occurr = 0;
open(FILE,">$doc.lemmes");
while($ligne = <ENTREE>) {
	if ( $mot ne $ligne ) {
		if ( $mot ne "" ) {
			$mot = substr($mot, 0, length($mot)-1);
			#print "$mot\t$occurr\n";
			print FILE "$mot\t$occurr\n";
		}
		$occurr = 1;
		$mot = $ligne;
	} else {
		$occurr++;
	}
}
# Affichage du dernier mot
$mot = substr($mot, 0, length($mot)-1);
#print "$mot\t$occurr\n";
print FILE "$mot\t$occurr\n";