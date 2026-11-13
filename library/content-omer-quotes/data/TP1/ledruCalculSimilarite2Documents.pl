#!/usr/bin/perl -w

use strict;

# support pour le français
use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;


my %similarites;



sub calculSimilarite2Documents {

#if ($_ != 2) {
#	die "Usage: calcul-documentFrenquency.pl <FICHIER_POIDS> <FICHIER_POIDS>\n";
#}


# on remplit un tableau associatif mot / poids pour le fichier 1
my $fichier_poids_1 = $_[0];
my %log_doc_poids_1;

open DOC_POIDS_1, "$fichier_poids_1" || die "Ne peut ouvrir le fichier $fichier_poids_1.\n";

my ($ligne, $terme, $poids);

# on remplit le hash
while ($ligne = <DOC_POIDS_1>) {
	$ligne =~ /^([^\t]+)\t(.+)$/;
	$terme = $1;
	$poids = $2;
	$log_doc_poids_1{$terme} = $poids;
}
close DOC_POIDS_1;


# meme chose pour le fichier de poids 2
my $fichier_poids_2 = $_[1];
my %log_doc_poids_2;

open DOC_POIDS_2, "$fichier_poids_2" || die "Ne peut ouvrir le fichier $fichier_poids_2.\n";

# on remplit le hash
while ($ligne = <DOC_POIDS_2>) {
	$ligne =~ /^([^\t]+)\t(.+)$/;
	$terme = $1;
	$poids = $2;
	$log_doc_poids_2{$terme} = $poids;
}
close DOC_POIDS_2;


# calcul de similarite
my ($mot, $Wijik, $Wij, $Wik, $poids1, $poids2, $sim);

$Wijik = 0;
$Wij = 0;
$Wik = 0;

foreach $mot (keys %log_doc_poids_1) {
	if (exists $log_doc_poids_2{$mot}) {
		$poids1 = $log_doc_poids_1{$mot};
		$poids2 = $log_doc_poids_2{$mot};
		$Wijik += ($poids1 * $poids2);
		# print "$mot\tpoids 1 : $poids1\t poids 2 : $poids2\n";
	}
}

# calcul du denominateur
my ($val, $val2);
foreach $val (values %log_doc_poids_1) {
	$Wij += ($val * $val);
}

foreach $val2 (values %log_doc_poids_2) {
	$Wik += ($val2 * $val2);
}

$Wij = sqrt($Wij);
$Wik = sqrt($Wik);

$sim = $Wijik / ($Wij * $Wik);
# print "$sim\n";
return $sim;

}




# fonction de tri
sub parSimilariteDecroissante {
	$similarites{$b} <=> $similarites{$a};
}



sub calculSimilaritesDocs {

my $fichier = $_[0];

my $rep = $_[1];
# on enlève un "/" terminal éventuel
$rep =~ s/\/$//;

my @fichiersPoids;
opendir CORPUS_REP, "$rep" or die "Ne peut ouvrir le répertoire du corpus: $rep.\n";
@fichiersPoids = grep /\.poids$/, readdir CORPUS_REP;

# pour chaque fichier de poids
my $sim;
my $fichierPoids;
foreach $fichierPoids (@fichiersPoids) {
	# on appelle la fonction calculSimilarite2Documents
	$sim = calculSimilarite2Documents($fichier, $fichierPoids);
	#print "$fichierPoids\t$sim\n";
	$similarites{$fichierPoids} = $sim;
}
my @resultatsTries;
@resultatsTries = sort parSimilariteDecroissante @fichiersPoids;

my $resultat;
foreach $resultat (@resultatsTries) {
	print "$resultat\t$similarites{$resultat}\n";
}

}






if (@ARGV != 2) {
	die "Usage: calcul-documentFrenquency.pl <FICHIER_POIDS> <REPERTOIRE>\n";
}
calculSimilaritesDocs($ARGV[0], $ARGV[1]);