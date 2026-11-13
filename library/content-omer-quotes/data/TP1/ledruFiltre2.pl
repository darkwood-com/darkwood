#!/usr/bin/perl -w

use strict;

my ($ligne, $mot, $lemme, $cat);

# pour chaque ligne sur le flux d'entrée
while ($ligne = <STDIN>) {
    # si la ligne est composé de trois colonnes séparées par des tabs
    # (soit, la sortie du tree-tagger)
    if ( $ligne =~ /(.*)\t(.*)\t(.*)/) {
	# on récupère chaque colonne
	$mot = $1; $lemme = $3; $cat = $2;
	# on ne met que le mot sur la sortie standard
	#if ( $cat ne "PUN" && $cat ne "SENT") {
	if ( $cat !~ /(PUN|SENT)/) {
		print "$mot\n";
	}
    }
}
