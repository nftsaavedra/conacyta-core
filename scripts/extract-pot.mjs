/**
 * Extract translatable strings from PHP and JS source files
 * into a WordPress .pot file.
 *
 * Run: node scripts/extract-pot.mjs
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(join(__dirname, '..'));
const textDomain = 'conacyta';

const fileExtensions = ['.php', '.js'];
const searchDirs = ['src', 'admin'];

function walk(dir) {
    const results = [];
    if (!existsSync(dir)) return results;
    for (const entry of readdirSync(dir)) {
        const full = join(dir, entry);
        const stat = statSync(full);
        if (stat.isDirectory()) {
            if (entry === 'node_modules' || entry === 'vendor' || entry === '.git' || entry === 'dist' || entry === 'build') continue;
            results.push(...walk(full));
        } else {
            if (fileExtensions.some(ext => entry.endsWith(ext))) {
                results.push(full);
            }
        }
    }
    return results;
}

const patterns = [
    /\b__\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\b__\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\b_e\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\b_e\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_html__\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_html__\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_attr__\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_attr__\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_html_e\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_html_e\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_attr_e\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'(conacyta)'\s*\)/g,
    /\besc_attr_e\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(conacyta)'\s*\)/g,
    /\b_x\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*'[^']*'\s*,\s*'(conacyta)'\s*\)/g,
    /\b_x\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'[^']*'\s*,\s*'(conacyta)'\s*\)/g,
];

function unescapePo(s) {
    return s
        .replace(/\\"/g, '"')
        .replace(/\\'/g, "'")
        .replace(/\\\\/g, '\\')
        .replace(/\\n/g, '\n')
        .replace(/\\t/g, '\t');
}

function escapePo(s) {
    return s
        .replace(/\\/g, '\\\\')
        .replace(/"/g, '\\"')
        .replace(/\n/g, '\\n')
        .replace(/\t/g, '\\t');
}

const entries = new Set();
const sources = new Map();

for (const dir of searchDirs) {
    const full = join(root, dir);
    const files = walk(full);
    for (const file of files) {
        let content;
        try {
            content = readFileSync(file, 'utf-8');
        } catch (e) {
            continue;
        }
        const relPath = file.replace(root + '\\', '').replace(/\\/g, '/');
        for (const pat of patterns) {
            pat.lastIndex = 0;
            let match;
            while ((match = pat.exec(content)) !== null) {
                const raw = match[1];
                const msgid = unescapePo(raw);
                if (!msgid.trim()) continue;
                if (msgid.includes('${')) continue;
                if (msgid.includes('\\')) continue;
                if (msgid.match(/^[A-Z_]+$/)) continue;
                entries.add(msgid);
                if (!sources.has(msgid)) {
                    sources.set(msgid, []);
                }
                const lineNum = content.substring(0, match.index).split('\n').length;
                sources.get(msgid).push(`${relPath}:${lineNum}`);
            }
        }
    }
}

const sortedEntries = Array.from(entries).sort((a, b) => a.localeCompare(b, 'es'));

const now = new Date();
const poRevisionDate = now.toISOString().replace(/\.\d{3}Z$/, '+00:00').replace(/Z$/, '+00:00');
const potRevisionDate = now.toISOString().replace(/\.\d{3}Z$/, '-00:00');

let pot = '';
pot += `msgid ""\n`;
pot += `msgstr ""\n`;
pot += `"Project-Id-Version: Conacyta Core 1.0.2\\n"\n`;
pot += `"Report-Msgid-Bugs-To: https://github.com/nftsaavedra/conacyta-core/issues\\n"\n`;
pot += `"POT-Creation-Date: ${potRevisionDate}\\n"\n`;
pot += `"MIME-Version: 1.0\\n"\n`;
pot += `"Content-Type: text/plain; charset=UTF-8\\n"\n`;
pot += `"Content-Transfer-Encoding: 8bit\\n"\n`;
pot += `"PO-Revision-Date: ${poRevisionDate}\\n"\n`;
pot += `"Language-Team: Spanish\\n"\n`;
pot += `"X-Domain: ${textDomain}\\n"\n`;
pot += `"X-Generator: conacyta-extract-pot\\n"`;
pot += `\n`;

for (const msgid of sortedEntries) {
    const refs = sources.get(msgid) || [];
    for (const ref of refs) {
        pot += `#: ${ref}\n`;
    }
    pot += `msgid "${escapePo(msgid)}"\n`;
    pot += `msgstr ""\n`;
    pot += `\n`;
}

const outDir = join(root, 'languages');
if (!existsSync(outDir)) {
    mkdirSync(outDir, { recursive: true });
}
const outFile = join(outDir, `${textDomain}.pot`);

writeFileSync(outFile, pot, 'utf-8');

console.log(`Extracted ${sortedEntries.length} unique strings to ${outFile}`);
console.log(`Encoding: UTF-8 without BOM`);
