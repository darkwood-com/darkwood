$file = $argv[0];
system("tree-tagger $file > $file/tag");
open(FILE, " < $file.tag");
while ( <FILE> ) {
	if (/(.*)\t(.*)\t(.*)/) {
		$mot = $1;
		print "$mot\t$filename\n";
	}
}
close(FILE);