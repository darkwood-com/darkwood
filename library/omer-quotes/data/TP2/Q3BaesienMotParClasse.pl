#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;
use strict;

#ici, Q3BaesienMotParClasse.pl = Q1Baesien,
#sauf que l'on n'applique aucun filtre a la lecture des textes

#commande perl Q3BaesienMotParClasse.pl 20news-bydate-supervisedQ3/

if (@ARGV != 1) { die "Usage : <DIR_SUPERVISED_WORLD_CLASS>\n"; }

my $dir_supervised_words_class = $ARGV[0];

#pourcentage des fichier que l'on va apprendre sur les fichier a classer
my $pourcentageFichierAppris = 90;
print("Pourcentage d'apprentissage des documents du corpus = $pourcentageFichierAppris %\n");

my $epsilon = 0.05; #parametre de lissage de laplace
print("Parametre de laplace = $epsilon\n");

#retourne un texte d'un fichier
sub openFile
{
  my $fichier = $_[0];
  chomp($fichier);
  
  my $text = "";
  my $ligne;
  
  open OPEN_FILE, $fichier;
  while($ligne = <OPEN_FILE>)
  {
    $text .= $ligne;
  }
  close OPEN_FILE;
  
  return $text;
}

my %words = (); #tous les mots existants ponderes de leur frequence
my @class = (); #tableau des classes contenant la frequence de chaque mot de la classe
my @classNB = (); #tableau des classes contenant le nombre de mot de la class

my $nbTypeWords = 0; #nombre de type de mots
my $iClassCurr = 0;
my $iClass = 0;
my $nbClass = 0;

# --> Apprentissage du corpus supervise
open (DOSSIERS_CLASS, "ls $dir_supervised_words_class | ");
my @ArrayDossier = <DOSSIERS_CLASS>;
close DOSSIERS_CLASS;
$nbClass = @ArrayDossier;
foreach my $nomClass(@ArrayDossier)
{
  chomp($nomClass);
  
  $classNB[$iClassCurr] = 0;
    
  #on lit les fichiers du dossier
  open (FICHIERS_CLASS, "ls ".$dir_supervised_words_class.$nomClass." | ");
  my @arrayFichier = <FICHIERS_CLASS>;
  close FICHIERS_CLASS;
  my $nbFichiers = @arrayFichier;
  my $nbFichiersALire = int($nbFichiers * $pourcentageFichierAppris / 100);
  my $nbFichiersLu = 0;
  
  print("Apprend le corpus : ".$nomClass." -> ".$nbFichiersALire." sur ".$nbFichiers." fichiers\n");
  
  foreach my $fichierClass(@arrayFichier)
  {
    $nbFichiersLu ++;
    #on apprend qu'un pourcentage du corpus
    if($nbFichiersLu > $nbFichiersALire) {next;}
    
    #on lit chaque fichier
    chomp($fichierClass);
    my $openText = openFile($dir_supervised_words_class.$nomClass."/".$fichierClass);
    my @mots = split(/ /, $openText);
    
    foreach my $newMot(@mots)
    {
      #on ajoute les mots que l'on a pas vu
      if(not exists $words{$newMot})
      {
        $words{$newMot} = 1;
        $nbTypeWords ++;
      }
      
      $classNB[$iClassCurr] ++;
      
      for($iClass = 0; $iClass < $nbClass; $iClass++)
      {
        if($iClass == $iClassCurr)
        {
          #on ajoute les mots par classe courante
          if(exists $class[$iClass]{$newMot}) {
            $class[$iClass]{$newMot} ++;
          } else {
            $class[$iClass]{$newMot} = 1 + $epsilon;
          }
        } else {
          #on prepare le lissage des autres classes
          if(not exists $class[$iClass]{$newMot}) {
            $class[$iClass]{$newMot} = $epsilon;
          }
        }
      }
    }
  }
  
  $iClassCurr ++;
}

#on ajoute $epsilon au nombre de mots differents pour le lissage de laplace
$nbTypeWords = $nbTypeWords * $epsilon;

#on calcule les frequence de chaque mot de chacune des classes
for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
{
  my $freqTotal = 0;
  foreach my $newMot(keys %{$class[$iClassCurr]})
  {
    $class[$iClassCurr]{$newMot} = $class[$iClassCurr]{$newMot} / ($classNB[$iClassCurr] + $nbTypeWords);
    $freqTotal = $freqTotal + $class[$iClassCurr]{$newMot};
  }
  
  #permet de verifier que l'on a bien une frequence de 1 pour chaque classe
  print("Frequence totale des mots de la classe $iClassCurr = $freqTotal\n");
}

# --> Lecture du corpus non supervise
my @logVraissemblance = (); #log vraissemblance de chaque classe
my @freqClassification = (); #frequence finale de la repartition des documents non supervise par classe
my @nbFreqClassification = (); #nombre de document par classe
my $iCurrentDoc = 0;

for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
{
  $freqClassification[$iClassCurr] = 0;
  $nbFreqClassification[$iClassCurr] = 0;
}

#on classe chaque document du corpus non supervise dans une des classes
my $iClassIt = 0;
foreach my $nomClass(@ArrayDossier)
{
  chomp($nomClass);
  
  #on lit les fichiers du dossier
  open (FICHIERS_CLASS, "ls ".$dir_supervised_words_class.$nomClass." | ");
  my @arrayFichier = <FICHIERS_CLASS>;
  close FICHIERS_CLASS;
  my $nbFichiers = @arrayFichier;
  my $nbFichiersALire = int($nbFichiers * $pourcentageFichierAppris / 100);
  my $nbFichiersLu = 0;
  
  $nbFreqClassification[$iClassIt] = $nbFichiers - $nbFichiersALire;
  
  print("Classe le corpus : ".$nomClass." -> ".($nbFichiers - $nbFichiersALire)." sur ".$nbFichiers." fichiers\n");
  
  foreach my $fichierClass(@arrayFichier)
  {
    $nbFichiersLu ++;
    #on apprend qu'un pourcentage du corpus
    if($nbFichiersLu <= $nbFichiersALire) {next;}
    
    my $openText = openFile($dir_supervised_words_class.$nomClass."/".$fichierClass);
    my @mots = split(/ /, $openText);
    
    #on remet les log vraissemblance a zero
    for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
    {
      $logVraissemblance[$iClassCurr] = 0;
    }
    
    #on ajoute la log vraissemblance de tous les mots rencontres sur chaque classe
    foreach my $newMot(@mots)
    {
      for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
      {
        if(exists $class[$iClassCurr]{$newMot})
        {
          $logVraissemblance[$iClassCurr] = $logVraissemblance[$iClassCurr] + log($class[$iClassCurr]{$newMot});
        }
      }
    }
    
    #on decide a quelle classe appartient le document
    my $iClassBest = 0;
    my $maxLogVraissemblance = -1000000000000000000;
    for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
    {
      if($maxLogVraissemblance < $logVraissemblance[$iClassCurr])
      {
        $maxLogVraissemblance = $logVraissemblance[$iClassCurr];
        $iClassBest = $iClassCurr;
      }
    }
    
    if($iClassIt == $iClassBest)
    {
      $freqClassification[$iClassBest] ++;
    }
    
  }
  
  $iClassIt ++;
}

#on affiche le taux de reussite 
for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
{
  $freqClassification[$iClassCurr] = $freqClassification[$iClassCurr] / $nbFreqClassification[$iClassCurr];
  print("Frequence des documents non supervises dans la classe $iClassCurr = $freqClassification[$iClassCurr]\n");
}