#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;
use strict;

#commande perl Q3RemplacerMotParClasse.pl 20news-bydate-supervisedQ3/ Q2resultats/100classes/Q2resultatsKMoyenne.txt

if (@ARGV != 2) { die "Usage : <DIR_SUPERVISED> <FICHIER_MOT_CLASS>\n"; }

my $dir_supervised = $ARGV[0];
my $file_mot_classe = $ARGV[1];

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

#retourne un texte d'un fichier dont on a filtre le contenu
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
  
  $text = filtrerCaracteres($text);
  
  return $text;
}

#ecrit le texte passe en parametre dans le fichier passe en parametre
sub WriteDocument
{
  my $fichier = $_[0];
  my $textDoc = $_[1];
  
  open(WRITE,">$fichier") || die("Cannot Open File");
  print WRITE $textDoc;
  close(WRITE);
}

my @class = (); #tableau des classes contenant les mots appartenant a ces classe (tableau x hash)

my $nbClass = -1;

# --> le fichier $file_mot_classe indique quelle classe est associee a chaque mot
my $ligne;
open OPEN_FILE_MOT_CLASSE, $file_mot_classe;
while($ligne = <OPEN_FILE_MOT_CLASSE>)
{
  if($ligne =~ /^- Classe.*/)
  {
    #on a rencontre une nouvelle classe
    $nbClass ++;
  }
  elsif ($ligne =~ /^[a-z].*/)
  {
    #la permiere lettre est une lettre de l'alphabet, donc c'est un mot
    chomp($ligne);
    $class[$nbClass]{$ligne} = 1;
  }
}
close OPEN_FILE_MOT_CLASSE;

# --> on remplace les mots de tous les fichiers du dossier supervise par leur classe
open (DOSSIERS_CLASS, "ls $dir_supervised | ");
my @ArrayDossier = <DOSSIERS_CLASS>;
close DOSSIERS_CLASS;
my $iClass = 0;
my $trouve = 0;
foreach my $nomClass(@ArrayDossier)
{
  chomp($nomClass);
  
  #on lit les fichiers du dossier
  open (FICHIERS_CLASS, "ls ".$dir_supervised.$nomClass." | ");
  my @arrayFichier = <FICHIERS_CLASS>;
  close FICHIERS_CLASS;
  my $nbFichiers = @arrayFichier;
  
  print("Traite le corpus : ".$nomClass." -> ".$nbFichiers." fichiers\n");
  
  foreach my $fichierClass(@arrayFichier)
  {
    #on lit chaque fichier
    chomp($fichierClass);
    my $openText = openFile($dir_supervised.$nomClass."/".$fichierClass);
    my @mots = split(/ /, $openText);
    
    my $writeText = ""; #fichier que l'on va reecrire en changeant les mots par leur classe
    foreach my $newMot(@mots)
    {
      #on ajoute les mots que l'on a pas vu
      $iClass = 0;
      $trouve = 0;
      while($iClass < $nbClass && $trouve == 0)
      {
        if(exists $class[$iClass]{$newMot}) {
          #on remplace le mot par sa classe
          $writeText .= "$iClass ";
          $trouve = 1;
        }
        $iClass ++;
      }
    }
    #if(not $writeText eq "")
    #{
    #  print($nomClass."/".$fichierClass."\n--> $writeText\n\n");
    #}
    
    #on reecrit le fichier
    WriteDocument($dir_supervised.$nomClass."/".$fichierClass, $writeText);
  }
}

