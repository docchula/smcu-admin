// Renders transcript HTML files to PDF using Playwright's Chromium.
// Usage: node scripts/render-transcript-pdf.mjs <manifest.json>
//
// The manifest's HTML files reference root-relative URLs (/assets/...) that resolve
// against nothing under file:// or page.setContent(), so each job is served from a
// fake origin instead: the document itself, and /assets/** from manifest.assetsDir.

import { chromium } from 'playwright';
import { readFile } from 'node:fs/promises';
import { createServer } from 'node:http';
import { extname, join } from 'node:path';

const ORIGIN = 'http://transcript.local';
const MIME = { '.ttf': 'font/ttf', '.svg': 'image/svg+xml', '.css': 'text/css' };

async function main() {
    const manifestPath = process.argv[2];
    if (!manifestPath) {
        console.error(JSON.stringify({ fatal: 'Missing manifest path argument' }));
        process.exit(1);
    }
    const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
    const { jobs, assetsDir, margin, fontBase64, footerDate } = manifest;

    const footerStyle = `
        <style>
            * { font-size: 8px; }
            @font-face { font-family: 'TH Sarabun New'; src: url('data:font/ttf;base64,${fontBase64}'); }
            div { font-family: 'TH Sarabun New', sans-serif; width: 100%; display: flex; justify-content: space-between; padding: 0 0.9in 0 0.7in; }
            span { flex: 1; }
            span:nth-child(2) { text-align: center; }
            span:last-child { text-align: right; }
        </style>`;
    const footerTemplateFor = (footerLeft) => `
        ${footerStyle}
        <div>
            <span>${footerLeft}</span>
            <span>หน้าที่ <span class="pageNumber"></span> จาก <span class="totalPages"></span></span>
            <span>${footerDate}</span>
        </div>`;

    let browser;
    try {
        browser = await chromium.launch();
    } catch (error) {
        console.error(JSON.stringify({ fatal: `Failed to launch Chromium: ${error.message}` }));
        process.exit(1);
    }

    try {
        const context = await browser.newContext();
        await context.route(`${ORIGIN}/**`, async (route, request) => {
            const path = new URL(request.url()).pathname;
            if (path.startsWith('/assets/')) {
                try {
                    const body = await readFile(join(assetsDir, decodeURIComponent(path.slice('/assets/'.length))));
                    return route.fulfill({ status: 200, contentType: MIME[extname(path)] ?? 'application/octet-stream', body });
                } catch {
                    return route.fulfill({ status: 404, body: '' });
                }
            }
            const job = jobs.find((j) => j.route === path);
            if (job) {
                const body = await readFile(job.htmlPath, 'utf8');
                return route.fulfill({ status: 200, contentType: 'text/html; charset=utf-8', body });
            }
            return route.fulfill({ status: 404, body: '' });
        });

        const page = await context.newPage();
        await page.addInitScript(() => {
            window.print = () => {};
        });

        for (const job of jobs) {
            try {
                await page.goto(`${ORIGIN}${job.route}`, { waitUntil: 'networkidle' });
                await page.pdf({
                    path: job.outputPath,
                    format: 'A4',
                    printBackground: true,
                    displayHeaderFooter: true,
                    headerTemplate: '<span></span>',
                    footerTemplate: footerTemplateFor(job.footerLeft),
                    margin,
                });
                console.log(JSON.stringify({ id: job.id, ok: true }));
            } catch (error) {
                console.log(JSON.stringify({ id: job.id, ok: false, error: error.message }));
            }
        }
    } finally {
        await browser.close();
    }
}

main();
