/**
 * Build distributable zip for Conacyta Core plugin.
 * Output: dist/conacyta-core-{version}.zip with conacyta-core/ as root.
 * Run: node scripts/zip-dist.mjs
 */
import AdmZip from 'adm-zip';
import { cpSync, existsSync, mkdirSync, readFileSync, rmSync, statSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(join(__dirname, '..'));

const exclude = [
  'node_modules',
  '.git',
  'AGENTS.md',
  '.gitignore',
  'assets',
  'package.json',
  'package-lock.json',
  'scripts',
  'src/admin',
  'dist',
  '*.zip',
  '*.rar',
  '*.log',
  '.env',
  '.env.local',
  '.cache',
  'admin/datetime-picker-test.png',
  'admin/date-fields-final.png',
  'src/admin/settings.js',
  '.DS_Store',
  'Thumbs.db',
];

function shouldExclude(relPath) {
  return exclude.some(pattern => {
    if (pattern.includes('*')) {
      const regex = new RegExp('^' + pattern.replace(/\./g, '\\.').replace(/\*/g, '.*') + '$');
      return regex.test(relPath);
    }
    return relPath === pattern || relPath.startsWith(pattern + '/');
  });
}

function readPluginVersion() {
  const php = readFileSync(join(root, 'conacyta-core.php'), 'utf-8');
  const match = php.match(/Version:\s*([\d.]+)/);
  return match ? match[1] : '0.0.0';
}

const version = readPluginVersion();
const tempDir = join(root, '..', '.conacyta-build-temp');
const tempPlugin = join(tempDir, 'conacyta-core');
const distDir = join(root, 'dist');
const outFile = join(distDir, `conacyta-core-${version}.zip`);

console.log(`Building conacyta-core v${version} distribution zip...`);

try {
  if (existsSync(tempDir)) {
    rmSync(tempDir, { recursive: true });
  }
  if (!existsSync(distDir)) {
    mkdirSync(distDir, { recursive: true });
  }

  cpSync(root, tempPlugin, {
    recursive: true,
    filter: (src) => {
      const rel = src.replace(root, '').replace(/^[\\/]/, '').replace(/\\/g, '/');
      if (rel === '') return true;
      return !shouldExclude(rel);
    },
  });

  const zip = new AdmZip();
  zip.addLocalFolder(tempPlugin, 'conacyta-core');
  zip.writeZip(outFile);

  const size = statSync(outFile).size;
  console.log(`Done: dist/conacyta-core-${version}.zip (${(size / 1024).toFixed(0)} KB)`);
} catch (e) {
  console.error('Error:', e.message);
  process.exit(1);
} finally {
  if (existsSync(tempDir)) {
    rmSync(tempDir, { recursive: true });
  }
}
