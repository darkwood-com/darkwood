"""Local-only Git privacy audit. Scanner output is redacted; no repository data is uploaded."""
import argparse
import datetime
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

parser = argparse.ArgumentParser()
parser.add_argument('--scanner', default='gitleaks')
parser.add_argument('--public-summary', type=Path)
args = parser.parse_args()
root = Path(__file__).resolve().parents[1]
private = root/'storage/app/private/security'
private.mkdir(mode=0o700, parents=True, exist_ok=True)
private.chmod(0o700)
report_dir = Path(tempfile.mkdtemp(prefix='audit-', dir=private))
report_dir.chmod(0o700)

def git(*values):
    return subprocess.check_output(['git', *values], cwd=root)

def paths(*values):
    return set(filter(None, git('ls-files', '-z', *values).decode().split('\0')))

indexed = paths('--cached')
working = indexed | paths('--others', '--exclude-standard')
private_reports = {'publication-report.json', 'publication-report.html', 'publication-static-exports.json',
                   'publication-static-validation.json', 'publication-download-cache.json',
                   'publication-manual-actions.json', 'publication-provider-blockers.json',
                   'publication-verification.json', 'speakerdeck-account-audit.json', 'slideshare-account-audit.json'}

def forbidden(name):
    p = Path(name)
    return ((p.name.startswith('.env') and name != '.env.example')
            or name == 'database/publication-registry.json'
            or (p.parent.as_posix() == 'docs' and p.name in private_reports)
            or (name.startswith('storage/') and p.name != '.gitignore')
            or p.name in ['id_rsa', 'id_ed25519', 'id_ecdsa', 'id_dsa', 'auth.json']
            or '.ssh' in p.parts
            or p.name.endswith(('.sql.gz', '.dump.gz'))
            or '.sqlite' in p.name
            or '.log.' in p.name
            or p.suffix.lower() in ['.pem', '.key', '.p12', '.pfx', '.sqlite', '.sql', '.dump', '.log', '.ppk', '.jks', '.keystore'])

summary = {'checked_at': datetime.datetime.now(datetime.timezone.utc).isoformat(),
           'head': git('rev-parse', 'HEAD').decode().strip(),
           'reachable_commits': int(git('rev-list', '--all', '--count')),
           'indexed_files': len(indexed), 'working_files': len(working),
           'boundary_violations': sorted(name for name in working if forbidden(name)), 'scans': []}
try:
    version = subprocess.run([args.scanner, 'version'], capture_output=True, text=True, check=True)
    summary['scanner'] = 'gitleaks '+version.stdout.strip()
    snapshot = report_dir/'working-snapshot'
    snapshot.mkdir(mode=0o700)
    for name in working:
        source = root/name
        if source.is_symlink() or not source.is_file():
            continue
        target = snapshot/name
        target.parent.mkdir(mode=0o700, parents=True, exist_ok=True)
        shutil.copyfile(source, target)
        target.chmod(0o600)
    try:
        for scope, scan in [('history', ['git', '--log-opts=--all --full-history', str(root)]),
                            ('index', ['git', '--pre-commit', '--staged', str(root)]),
                            ('worktree', ['dir', str(snapshot)])]:
            report = report_dir/(scope+'.json')
            result = subprocess.run([args.scanner, *scan, '--redact=100', '--no-banner', '--no-color',
                                     '--log-level=error', '--report-format=json', '--report-path='+str(report)],
                                    cwd=root, capture_output=True, text=True)
            if report.exists():
                report.chmod(0o600)
            findings = json.loads(report.read_text()) if report.exists() else []
            summary['scans'].append({'scope': scope, 'exit_code': result.returncode,
                                     'finding_count': len(findings)})
    finally:
        shutil.rmtree(snapshot)
except (OSError, subprocess.SubprocessError):
    summary['scanner_error'] = 'scanner_unavailable_or_failed'
summary['passed'] = (not summary['boundary_violations'] and not summary.get('scanner_error')
                     and len(summary['scans']) == 3
                     and all(scan['exit_code'] == 0 and scan['finding_count'] == 0 for scan in summary['scans']))
file = report_dir/'summary.json'
file.write_text(json.dumps(summary, indent=2)+'\n')
file.chmod(0o600)
if args.public_summary:
    # Counts and paths already present in Git only; no matches, credentials or local report paths.
    args.public_summary.write_text(json.dumps(summary, indent=2)+'\n')
print(json.dumps(summary, indent=2))
raise SystemExit(0 if summary['passed'] else 1)
