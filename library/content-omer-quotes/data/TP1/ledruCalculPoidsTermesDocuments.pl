#!/usr/bin/perl

use strict;

# support pour le français
use POSIX qw(locale_h);
POSIX::setlocale("LC_TYPE", "fr_FR");
use locale;

if (@ARGV != 2) {
	die "Usage: calcul-documentFrenquency.pl <REPERTOIRE> <FICHIER_DOCFREQ>\n";
}

my $repertoire = $ARGV[0];
# on enlève un "/" terminal éventuel
$repertoire =~ s/\/$//;

my $fichier_docFreq = $ARGV[1];
my $nbDocs; # le nombre de documents dans le corpus
my %log_docFreq; # hash ayant pour clé des termes et pour valeur leur log(N/dfi)

# tout d'abord, on remplit un hash pour mémoriser les document frequency une fois pour toutes
open DOC_FREQ, "$fichier_docFreq" || die "Ne peut ouvrir le fichier $fichier_docFreq.\n";

# on récupère le nombre de documents
# note: on aurait tout aussi bien pu mémoriser dans le fichier log(N/dfi)
# on va en profiter pour faire le calcul une fois pour toute dans le hash
<DOC_FREQ> =~ /N=(\d+)$/;
$nbDocs = $1;

my ($ligne, $terme, $nbOccs);

# on remplit le hash
while ($ligne = <DOC_FREQ>) {
	$ligne =~ /^([^\t]+)\t(.+)$/;
	$terme = $1;
	$nbOccs = $2;
	$log_docFreq{$terme} = log($nbDocs / $nbOccs);
}
close DOC_FREQ;

# maintenant on transforme chacun des fichiers .lemme en .poids

my @fichiersLemmes; # la liste des fichiers de lemmes
my ($fichierLemmes, $fichierPoids);
my $poids;
opendir CORPUS_REP, "$repertoire" or die "Ne peut ouvrir le répertoire du corpus: $repertoire.\n";
@fichiersLemmes = grep /\.lemmes$/, readdir CORPUS_REP;

# pour chaque fichier de lemmes
foreach $fichierLemmes (@fichiersLemmes) {
	# on calcule le nom du fichier de poids correspondant
	$fichierLemmes =~ /^(.*).lemmes$/;
	$fichierPoids = $1.".poids";

	# en cas d'échec d'ouverture du fichier, on ne fait rien 
	# => pas de fichier de poids, donc pas de recherche possible sur ce fichier

	if (open FICHIER_LEMMES, "$repertoire/$fichierLemmes") {
		if (open FICHIER_POIDS, "> $fichierPoids") {
			while ($ligne = <FICHIER_LEMMES>) {
				$ligne =~ /^([^\t]+)\t(\d+)$/;
				$terme = $1;
				$nbOccs = $2;
				$poids = $nbOccs * $log_docFreq{$terme}; # w = tf.idf
				print FICHIER_POIDS "$terme\t$poids\n";
			}
			close FICHIER_POIDS;
		}
		close FICHIER_LEMMES;
	}
	else {
		print STDERR "Le fichier de poids n'a pu être construit pour $fichierLemmes.\n";
	}
}

closedir CORPUS_REP;
