$motcourant = "";
@docscourants = ();
while ( $ligne = <STDIN> ) {
	if ( $ligne =~ /^([^\t]*)\t([^\t]*)$/ ) {
		$doc = $2; chomp $doc;
		if ( $1 eq $motcourant ) {
			push @docscourant, $doc;
		} else {
			if ( $motcourant ne "" ) {
				print "$motcourant\t", join("; ", @docscourants);
			}
			$motcourant = $1;
			@docscourant = ($doc);
		}
	}
}