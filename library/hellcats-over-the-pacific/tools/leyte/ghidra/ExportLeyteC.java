// Leyte Gulf overlay, after auto-analysis: every function whose entry lies in the overlay's code
// (file offsets [0x20, 0x20 + code size)) decompiled, in address order, into one C file (as the
// protected Pacific Conflict.c export), and an index of entries and body sizes (JSON).
// Args: the C output path, the index output path, the code end (hex file offset).
//@category Hellcats
import ghidra.app.decompiler.DecompInterface;
import ghidra.app.decompiler.DecompileResults;
import ghidra.app.script.GhidraScript;
import ghidra.program.model.listing.Function;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.List;

public class ExportLeyteC extends GhidraScript {
	@Override
	public void run() throws Exception {
		String[] args = getScriptArgs();
		long end = Long.decode(args[2]);
		// Optional: the code's first offset (0x20 for the overlay, after its header; 4 for a CODE
		// segment) and the C file's title line.
		long start = args.length > 3 ? Long.decode(args[3]) : 0x20;
		String title = args.length > 4 ? args[4] : "Leyte Gulf overlay (Leyte Gulf:Leyte Gulf data fork)";
		DecompInterface d = new DecompInterface();
		d.openProgram(currentProgram);
		List<Function> fs = new ArrayList<>();
		for (Function f : currentProgram.getFunctionManager().getFunctions(true)) {
			long e = f.getEntryPoint().getOffset();
			if (e >= start && e < end) fs.add(f);
		}
		int ok = 0;
		try (PrintWriter c = new PrintWriter(args[0], "UTF-8"); PrintWriter idx = new PrintWriter(args[1], "UTF-8")) {
			c.println("// " + title + ", Ghidra " + getGhidraVersion()
					+ " headless: Motorola 68000 BE, raw binary at base 0 (file offsets), tools/leyte/ghidra.");
			idx.println("[");
			for (int i = 0; i < fs.size(); i++) {
				Function f = fs.get(i);
				DecompileResults r = d.decompileFunction(f, 120, monitor);
				boolean good = r != null && r.decompileCompleted();
				if (good) ok++;
				c.println();
				c.println("// " + f.getName() + " @ 0x" + Long.toHexString(f.getEntryPoint().getOffset()));
				c.println(good ? r.getDecompiledFunction().getC() : "// decompilation failed: " + (r == null ? "null" : r.getErrorMessage()));
				idx.println(String.format("  {\"name\": \"%s\", \"entry\": \"0x%x\", \"size\": %d, \"decompiled\": %s}%s",
						f.getName(), f.getEntryPoint().getOffset(), f.getBody().getNumAddresses(), good, i + 1 < fs.size() ? "," : ""));
			}
			idx.println("]");
		}
		println("exported " + fs.size() + " functions, " + ok + " decompiled");
	}
}
