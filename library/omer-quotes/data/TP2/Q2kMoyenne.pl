#!/usr/bin/perl -w

use POSIX qw(locale_h);
POSIX::setlocale(LC_CTYPE, "fr_FR");
use locale;
use strict;

#commande perl Q2kMoyenne.pl 20news-bydate-unsupervised/ 10

if (@ARGV != 2) { die "Usage : <DIR_UNSUPERVISED> <kMax>\n"; }

my $dir_unsupervised = $ARGV[0]; #dossier non supervise
my $kMax = $ARGV[1]; #nombre de classe

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

open DOSSIER, "ls $dir_unsupervised* |";
my @documents = <DOSSIER>; #liste de tous les documents
close DOSSIER;
my $nbDocuments = @documents; #nb de documents


my %IDF = (); #vecteur d'IDF pour chaque composante de mot
my %TF = (); #vecteur de TF par rapport a un document pour chaque composante de mot
my @TF_IDF = (); #matrice de TF-IDF (document x mot) -> tableau de hash (creux)

my $texteWrite = ""; #ce qui sera ecrit dans le texte final exporte

#calcul de l'IDF de chaque terme dans les documents
#rappel : IDF = log(nombre de documents / nombre de documents contenant le terme)
for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
{
  my $openText = openFile($documents[$iDoc]);
  my @mots = split(/ /, $openText);
  my %motDejaVu = ();
  
  foreach my $newMot(@mots)
  {
    if(not exists $motDejaVu{$newMot})
    {
      #le terme est compte qu'une seule fois par document
      $motDejaVu{$newMot} = 1;
      if(not exists $IDF{$newMot})
      {
        $IDF{$newMot} = 1;
      }
      else
      {
        $IDF{$newMot} = $IDF{$newMot} + 1;
      }
    }
  }
}

my $nbMotsJete = 0; #mot que l'on a jete
my $nbMotsAClasser = 0; #nombre de mots a classer

foreach my $newMot(keys %IDF)
{
  #on en profite pour virer les mots d'occurence inferieure a 5
  if($IDF{$newMot} <= 5)
  {
    delete $IDF{$newMot};
    $nbMotsJete ++;
  }
  else 
  {
    $IDF{$newMot} = log($nbDocuments / $IDF{$newMot});
    $nbMotsAClasser ++;
  }
}

$texteWrite .= "# Nombre de mots jete      : $nbMotsJete\n";
$texteWrite .= "# Nombre de mots a classer : $nbMotsAClasser\n";

print("Nombre de mots jete      : $nbMotsJete\n");
print("Nombre de mots a classer : $nbMotsAClasser\n");

#calcul du TF-IDF par rapport a un mot et un document
print("Calcul du TF-IDF\n");
my $val = 0;
for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
{
  if($iDoc % 500 == 0)
  {
    print("$iDoc sur $nbDocuments ...\n");
  }
  
  #met dans la variable %TF les TF des termes du document qui sont dans l'IDF
  #rappel : TF = frequence du terme du document / nombre de terme du document
  %TF = ();
  my $openText = openFile($documents[$iDoc]);
  my @mots = split(/ /, $openText);
  my $nbMots = @mots;
  
  foreach my $newMot(@mots)
  {
    if(exists $IDF{$newMot})
    {
      if(not exists $TF{$newMot})
      {
        $TF{$newMot} = 1;
      }
      else
      {
        $TF{$newMot} = $TF{$newMot} + 1;
      }
    }
  }
  
  foreach my $newMot(keys %TF)
  {
    $TF{$newMot} = $TF{$newMot} / $nbMots;
  }
  
  #calcul du TF-IDF
  my $normalisation = 0;
  foreach my $newMot(keys %TF)
  {
    #note : par rapport a ce qui precede, on est sur que $TF_IDF[$iDoc]{$newMot} existe
    $val = $TF{$newMot} * $IDF{$newMot};
    $TF_IDF[$iDoc]{$newMot} = $val;
    $normalisation = $normalisation + $val * $val;
  }
  $normalisation = sqrt($normalisation);
  
  #on normalise le TF-IDF
  foreach my $newMot(keys %TF)
  {
    #note : par rapport a ce qui precede, on est sur que $TF_IDF[$iDoc]{$newMot} existe
    $TF_IDF[$iDoc]{$newMot} = $TF_IDF[$iDoc]{$newMot} / $normalisation;
  }
}

# --> algorithme des k-moyennes
print("Algorithme des k-moyennes\n");
my %classeDuMot = (); #classe d'un mot
my @moy = (); #tableau des vecteurs moyennes -> matrice (tableau x tableau)
my @newMoy = (); #tableau des vecteurs nouvelles moyennes -> matrice (tableau x tableau)
my @moySum = ();

my @motsTypique = (); #les 5 mots les plus typiques de chaque classe -> matrice (classe x mot)
my @distanceMotsTypique = (); #distance des 5 mots les plus typiques de chaque classe
my @preCalculTF_IDF = (); #servira pour accelerer un peu le programme

my $HIGH_NUM = 100000000000000000000000000000;

#initialisation des moyennes
print("Initialisation des moyennes ...\n");
for(my $classNb = 0; $classNb < $kMax; $classNb++)  
{  
  for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
  {
    $moy[$classNb][$iDoc] = 0;
  }
  $moySum[$classNb] = 0;
  
  for(my $iMotTypique = 0; $iMotTypique < 5; $iMotTypique++)
  {
    $motsTypique[$classNb][$iMotTypique] = "*Aucun*";
    $distanceMotsTypique[$classNb][$iMotTypique] = $HIGH_NUM;
  }
}

#on distribue les mots aleatoirement dans des classes
#et on calcule les moyennes des classes
print("Distribution des mots aleatoirement dans les classes ...\n");
my $Avancement = 0;
foreach my $newMot(keys %IDF)
{
  my $classRand = int(rand($kMax));
  $classeDuMot{$newMot} = $classRand; #on affecte la classe du mot
  for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
  {
    if(defined (my $valTF_IDF =  $TF_IDF[$iDoc]{$newMot}))
    {
      $moy[$classRand][$iDoc] = $moy[$classRand][$iDoc] + $valTF_IDF;
    }
  }
  $moySum[$classRand] ++;
  
  if($Avancement % 1000 == 0)
  {
    print("$Avancement sur $nbMotsAClasser ...\n");
  }
  
  $Avancement ++;
}
#normalisation des moyennes
for(my $classNb = 0; $classNb < $kMax; $classNb++)  
{  
  for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
  {
    $moy[$classNb][$iDoc] = $moy[$classNb][$iDoc] / $moySum[$classNb];
  }
}

#boucle de l'algo de k-moyenne
#booleen qui permet de dire si l'ago a converge ou pas
#il y a convergence des lorsque auncun mot ne change de classe entre deux iterations succesives
my $converge = 0;
my $iterationAlgo = 0; #indique le nombre d'iterations des k-moyennes
while($converge == 0) 
{
  print("Iteration des kMoyennes : $iterationAlgo\n");
  
  #en meme temps que l'on va affecter les mots a des classes,
  #on va calculer les nouvelles moyennes de l'iteration suivante
  print("Initialisation des nouvelles moyennes\n");
  for(my $classNb = 0; $classNb < $kMax; $classNb++)  
  {  
    for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
    {
      $newMoy[$classNb][$iDoc] = 0;
    }
    $moySum[$classNb] = 0;
    
    for(my $iMotTypique = 0; $iMotTypique < 5; $iMotTypique++)
    {
      $motsTypique[$classNb][$iMotTypique] = "*Aucun*";
      $distanceMotsTypique[$classNb][$iMotTypique] = $HIGH_NUM;
    }
  }
  
  $Avancement = 0;
  #mise a jour des affectations aux classes
  $converge = 1; #on dit que l'algo converge
  print("Mise a jour des classes des mots\n");
  foreach my $newMot(keys %IDF)
  {
    if($Avancement % 50 == 0)
    {
      print("$Avancement sur $nbMotsAClasser ...\n");
    }
    $Avancement ++;
    
    #on pre-enregistre les valeurs du TF-IDF pour accelerer un peu l'algo
    for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
    {
      $preCalculTF_IDF[$iDoc] = 0;
      if(defined (my $valTF_IDF =  $TF_IDF[$iDoc]{$newMot}))
      {
        $preCalculTF_IDF[$iDoc] = $valTF_IDF;
      }
    }
    
    my $distance = 0;
    my $diff = 0;
    my $distanceMin = $HIGH_NUM;
    
    my $classeMotAvant = $classeDuMot{$newMot};
    my $classeMotApres = $classeMotAvant;
    
    #on affecte la classe la plus proche au mot
    for(my $classNb = 0; $classNb < $kMax; $classNb++)  
    {
      #on calcule la distance de la classe au mot
      $distance = 0;
      
      my $iDoc = 0;
      #petite astuce : on ne termine pas le calcul de la distance
      #si celle ci devient plus grande que la distance minimale
      while($iDoc < $nbDocuments && $distance < $distanceMin)
      {
        $diff = $moy[$classNb][$iDoc] - $preCalculTF_IDF[$iDoc];
        $distance = $distance + $diff * $diff; #note : on laisse la distance au carre
        
        $iDoc ++;
      }
      
      #on regarde si cette classe est plus proche du mot que la classe actuelle
      if($distance < $distanceMin)
      {
        $distanceMin = $distance;
        
        #mise a jour de la classe du mot
        #on affecte le mot a la nouvelle classe
        $classeMotApres = $classNb;
      }
    }
    
    #on met a jour la classe du mot trouvee
    $classeDuMot{$newMot} = $classeMotApres;
    
    #on complete les nouvelles moyennes
    for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
    {
      $newMoy[$classeMotApres][$iDoc] = $newMoy[$classeMotApres][$iDoc] + $preCalculTF_IDF[$iDoc];
    }
    $moySum[$classeMotApres] ++;
    
    #si la classe a change d'au moins un mot entre deux iterations, alors l'algo n'a pas converge
    if($classeMotApres != $classeMotAvant)
    {
      $converge = 0;
    }
    
    #on met a jour les mots typiques de la meilleure classe affectee au mot
    my $trouve = 0;
    my $iMotTypique = 0;
    while($iMotTypique < 5 && $trouve == 0)
    {
      if($distanceMotsTypique[$classeMotApres][$iMotTypique] > $distance)
      {
        #on decale les mots
        for(my $iMotDecal = 4; $iMotDecal >= $iMotTypique; $iMotDecal--)
        {
          $distanceMotsTypique[$classeMotApres][$iMotDecal+1] = $distanceMotsTypique[$classeMotApres][$iMotDecal];
          $motsTypique[$classeMotApres][$iMotDecal+1] = $motsTypique[$classeMotApres][$iMotDecal];
        }
        #on insere le nouveau mot typique
        $distanceMotsTypique[$classeMotApres][$iMotTypique] = $distance;
        $motsTypique[$classeMotApres][$iMotTypique] = $newMot;
        $trouve = 1;
      }
      $iMotTypique ++;
    }
  }
  
  #on affiche les 5 mots les plus typiques de chaque classe si on a converge
  if($converge == 1)
  {
    $texteWrite .= "# $iterationAlgo iterations\n";
    $texteWrite .= "# $kMax classes\n# \n";
    for(my $classNb = 0; $classNb < $kMax; $classNb++)  
    {
      $texteWrite .= "# 5 premiers mots typiques de la classe $classNb [Ordre Croissant]\n";
      print("5 premiers mots typiques de la classe $classNb [Ordre Croissant]\n");
      for(my $iMotTypique = 0; $iMotTypique < 5; $iMotTypique++)
      {
        $texteWrite .= "# $motsTypique[$classNb][$iMotTypique]\n";
        print("$motsTypique[$classNb][$iMotTypique]\n");
      }
    }
    $texteWrite .= "\n";
  }
  
  #on normalise les nouvelles moyennes directement
  #dans le tableau des moyennes de la prochaine iteration
  for(my $classNb = 0; $classNb < $kMax; $classNb++)
  {
    for(my $iDoc = 0; $iDoc < $nbDocuments; $iDoc++)
    {
      $moy[$classNb][$iDoc] = $newMoy[$classNb][$iDoc] / $moySum[$classNb];
    }
  }
  
  $iterationAlgo ++;
}

#finalement, on enregistre dans un fichier les classes attribuees a chaque mot
my @MotsClass = ();
for(my $classNb = 0; $classNb < $kMax; $classNb++)  
{
  $MotsClass[$classNb] = "- Classe $classNb\n";
}

#on trie l'enregistrement par classe
foreach my $newMot(keys %IDF)
{
  $MotsClass[$classeDuMot{$newMot}] .= "$newMot\n";
}

#on assemble le texte final
for(my $classNb = 0; $classNb < $kMax; $classNb++)  
{
  $texteWrite .= $MotsClass[$classNb]."\n";
}

#on enregistre le fichier dans le dossier courrant
WriteDocument("Q2resultatsKMoyenne.txt", $texteWrite);