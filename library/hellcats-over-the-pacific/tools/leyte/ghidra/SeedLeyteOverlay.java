// Leyte Gulf overlay, before auto-analysis: a function at every seed (file offsets: the overlay entry
// and the Leyte offsets of correspondence.json's matched Pacific Conflict functions), so analysis
// reaches the rest of the code through their calls. Args: the seed file (one hex offset a line).
//@category Hellcats
import ghidra.app.script.GhidraScript;
import ghidra.program.model.address.Address;
import java.nio.file.Files;
import java.nio.file.Paths;

public class SeedLeyteOverlay extends GhidraScript {
	@Override
	public void run() throws Exception {
		int n = 0;
		for (String line : Files.readAllLines(Paths.get(getScriptArgs()[0]))) {
			line = line.trim();
			if (line.isEmpty()) continue;
			Address a = toAddr(Long.decode(line));
			disassemble(a);
			if (getFunctionAt(a) == null && createFunction(a, null) != null) n++;
		}
		println("seeded " + n + " functions");
	}
}
