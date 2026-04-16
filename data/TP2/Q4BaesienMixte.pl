#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;
use strict;

#ici, Q4BaesienMixte = alpha * Q1Baesien + (1 - alpha ) * Q3BaesienMotParClasse.pl

#commande perl Q4BaesienMixte.pl 20news-bydate-supervised/ 20news-bydate-supervisedQ3/ Q2resultats/100classes/Q2resultatsKMoyenne.txt

if (@ARGV != 3) { die "Usage : <DIR_SUPERVISED> <DIR_SUPERVISED_WORLD_CLASS> <FICHIER_MOT_CLASS>\n"; }

my $dir_supervised = $ARGV[0];
my $dir_supervised_words_class = $ARGV[1];
my $file_mot_classe = $ARGV[2];

#pourcentage des fichier que l'on va apprendre sur les fichier a classer
my $pourcentageFichierAppris = 90;
print("Pourcentage d'apprentissage des documents du corpus = $pourcentageFichierAppris %\n");

my $epsilon = 0.05; #parametre de lissage de laplace
print("Parametre de laplace = $epsilon\n");

#alpha est compris entre [0,1]
my $IAlpha = 0.1; #interval entre deux prises de mesure alpha pour la combinaison linaire des deux modeles
print("Intervale entre deux prises de mesures Alpha = $IAlpha\n");

#parametre de filtrage d'un texte
sub filtrerCaracteres
{
  my $text = $_[0];
  
  #on vire les caracteres speciaux
  $text =~ s/[.!?\"\';:%&{}(),\n<>_=#~\$\+\-\*\t^\/\\|\[\]]//g; #"
  
  #on vire les chiffres
  $text =~ s/[0-9]//g;
  
  #on rend insensible a la casse
  $text =~ tr/A-Z/a-z/;
  
  #on vire des  mots outils
  $text =~ s/\b(cannot|into|our|thus|about|co|is|ours|above|could|ourselves|together|across|down|out|too|after|during|itself|over|toward|afterwards|each|last|own|towards|again|eg|latter|per|under|against|either|latterly|perhaps|until|all|else|least|rather|up|almost|elsewhere|less|same|upon|alone|enough|ltd|seem|us|along|etc|many|seemed|very|already|even|may|seeming|via|also|ever|me|seems|was|although|every|meanwhile|several|we|always|everyone|might|she|well|among|everything|more|should|were|amongst|everywhere|moreover|since|what|except|most|so|whatever|few|mostly|some|when|another|first|much|somehow|whence|any|must|someone|whenever|anyhow|former|my|something|where|anyone|formerly|myself|sometime|whereafter|anything|namely|sometimes|whereas|anywhere|further|neither|somewhere|whereby|are|had|never|still|wherein|around|has|nevertheless|such|whereupon|have|next|than|wherever|he|no|whether|be|hence|nobody|whither|became|her|none|which|because|here|noone|them|while|become|hereafter|nor|themselves|who|becomes|hereby|not|then|whoever|becoming|herein|nothing|thence|whole|been|hereupon|now|whom|before|hers|nowhere|thereafter|whose|beforehand|herself|thereby|why|behind|him|off|therefore|will|being|himself|often|therein|below|his|thereupon|within|beside|how|once|these|without|besides|however|one|they|would|between|i|only|this|yet|beyond|ie|onto|those|you|both|if|though|your|other|through|yours|by|inc|others|throughout|yourself|can|indeed|otherwise|thru|yourselves|as|that|their|there|with|for|at|to|of|the|a|an|in|from|it|and|but|or|its|on)\b//gi;
  
  #on vire les doubles espaces
  $text =~ s/ +/ /g;
  
  #ainsi que le premier espace du debut et de fin
  $text =~ s/^ +//g;
  $text =~ s/ +$//g;
  
  return $text;
}

#retourne un texte d'un fichier
sub openFile
{
  my $fichier = $_[0];
  my $filtreTexte = $_[1];
  
  chomp($fichier);
  
  my $text = "";
  my $ligne;
  
  open OPEN_FILE, $fichier;
  while($ligne = <OPEN_FILE>)
  {
    $text .= $ligne;
  }
  close OPEN_FILE;
  
  if($filtreTexte == 1)
  {
    $text = filtrerCaracteres($text);
  }
  
  return $text;
}

my %words = (); #tous les mots existants ponderes de leur frequence
my @class = (); #tableau des classes contenant la frequence de chaque mot de la classe
my @classNB = (); #tableau des classes contenant le nombre de mot de la class

my $nbTypeWords = 0; #nombre de type de mots
my $iClassCurr = 0;
my $iClass = 0;
my $nbClass = 0;

# --> Apprentissage du corpus supervise non deteriore
print("Apprentissage du corpus non deteriore $dir_supervised\n");
open (DOSSIERS_CLASS, "ls $dir_supervised | ");
my @ArrayDossier = <DOSSIERS_CLASS>;
close DOSSIERS_CLASS;
$nbClass = @ArrayDossier;
foreach my $nomClass(@ArrayDossier)
{
  chomp($nomClass);
  
  $classNB[$iClassCurr] = 0;
  
  #on lit les fichiers du dossier
  open (FICHIERS_CLASS, "ls ".$dir_supervised.$nomClass." | ");
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
    my $openText = openFile($dir_supervised.$nomClass."/".$fichierClass, 1);
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

%words = (); #tous les mots existants ponderes de leur frequence
my @classDet = (); #tableau des classes contenant la frequence de chaque mot de la classe
my @classNBDet = (); #tableau des classes contenant le nombre de mot de la class

my $nbTypeWordsDet = 0; #nombre de type de mots
$iClassCurr = 0;

# --> Apprentissage du corpus supervise deteriore
print("Apprentissage du corpus deteriore $dir_supervised_words_class\n");
open (DOSSIERS_CLASS, "ls $dir_supervised_words_class | ");
@ArrayDossier = <DOSSIERS_CLASS>;
close DOSSIERS_CLASS;
$nbClass = @ArrayDossier;
foreach my $nomClass(@ArrayDossier)
{
  chomp($nomClass);
  
  $classNBDet[$iClassCurr] = 0;
  
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
    my $openText = openFile($dir_supervised_words_class.$nomClass."/".$fichierClass, 0);
    my @mots = split(/ /, $openText);
    
    foreach my $newMot(@mots)
    {
      #on ajoute les mots que l'on a pas vu
      if(not exists $words{$newMot})
      {
        $words{$newMot} = 1;
        $nbTypeWordsDet ++;
      }
      
      $classNBDet[$iClassCurr] ++;
      
      for($iClass = 0; $iClass < $nbClass; $iClass++)
      {
        if($iClass == $iClassCurr)
        {
          #on ajoute les mots par classe courante
          if(exists $classDet[$iClass]{$newMot}) {
            $classDet[$iClass]{$newMot} ++;
          } else {
            $classDet[$iClass]{$newMot} = 1 + $epsilon;
          }
        } else {
          #on prepare le lissage des autres classes
          if(not exists $classDet[$iClass]{$newMot}) {
            $classDet[$iClass]{$newMot} = $epsilon;
          }
        }
      }
    }
  }
  
  $iClassCurr ++;
}

#on ajoute $epsilon au nombre de mots differents pour le lissage de laplace
$nbTypeWordsDet = $nbTypeWordsDet * $epsilon;

#on calcule les frequence de chaque mot de chacune des classes
for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
{
  my $freqTotal = 0;
  foreach my $newMot(keys %{$classDet[$iClassCurr]})
  {
    $classDet[$iClassCurr]{$newMot} = $classDet[$iClassCurr]{$newMot} / ($classNBDet[$iClassCurr] + $nbTypeWordsDet);
    $freqTotal = $freqTotal + $classDet[$iClassCurr]{$newMot};
  }
  
  #permet de verifier que l'on a bien une frequence de 1 pour chaque classe
  print("Frequence totale des mots de la classe $iClassCurr = $freqTotal\n");
}

my @classFromMot = (); #tableau des classes contenant les mots appartenant a ces classe (tableau x hash)

my $nbClassFromMot = -1;

# --> le fichier $file_mot_classe indique quelle classe est associee a chaque mot
my $ligne;
open OPEN_FILE_MOT_CLASSE, $file_mot_classe;
while($ligne = <OPEN_FILE_MOT_CLASSE>)
{
  if($ligne =~ /^- Classe.*/)
  {
    #on a rencontre une nouvelle classe
    $nbClassFromMot ++;
  }
  elsif ($ligne =~ /^[a-z].*/)
  {
    #la permiere lettre est une lettre de l'alphabet, donc c'est un mot
    chomp($ligne);
    $classFromMot[$nbClassFromMot]{$ligne} = 1;
  }
}
close OPEN_FILE_MOT_CLASSE;

# --> Lecture du corpus non supervise
#on fait varier alpha entre 0 et 1 selon l'interval $IAlpha
for(my $alpha = 0; $alpha <= 1; $alpha += $IAlpha)
{
  print("LogVraissemblance pour alpha = $alpha\n");
  my @logVraissemblance = (); #log vraissemblance de chaque classe = log($alpha * P1 + (1 - $alpha) * P2)
  my $vraissemblance = 0; #calcule $alpha * P1 + (1 - $alpha) * P2
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
    open (FICHIERS_CLASS, "ls ".$dir_supervised.$nomClass." | ");
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
      
      # --> lecture des fichiers du corpus non deteriore
      my $openText = openFile($dir_supervised.$nomClass."/".$fichierClass, 1);
      my @mots = split(/ /, $openText);
      
      #on remet les log vraissemblance a zero
      for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
      {
        $logVraissemblance[$iClassCurr] = 0;
      }
      
      #on ajoute la log vraissemblance de tous les mots rencontres sur chaque classe
      foreach my $newMot(@mots)
      {
        #on cherche la classe correspondante a ce mot
        my $newClassFromMot = -1;
        for($iClassCurr = 0; $iClassCurr < $nbClassFromMot + 1; $iClassCurr++)
        {
          if(exists $classFromMot[$iClassCurr]{$newMot})
          {
            $newClassFromMot = $iClassCurr;
          }
        }
        
        for($iClassCurr = 0; $iClassCurr < $nbClass; $iClassCurr++)
        {
          #calcul de $alpha * P1 + (1 - $alpha) * P2
          $vraissemblance = 0;
          if(exists $class[$iClassCurr]{$newMot})
          {
            $vraissemblance = $alpha * $class[$iClassCurr]{$newMot};
          }
          
          if($newClassFromMot != -1)
          {
            if(exists $classDet[$iClassCurr]{$newClassFromMot})
            {
              $vraissemblance = $vraissemblance + (1 - $alpha) * $classDet[$iClassCurr]{$newClassFromMot};
              print("class $classDet[$iClassCurr]{$newClassFromMot}\n");
            }
          }
          
          if($vraissemblance > 0)
          {
            $logVraissemblance[$iClassCurr] = $logVraissemblance[$iClassCurr] + log($vraissemblance);
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
}