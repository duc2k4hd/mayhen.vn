import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import { test } from 'node:test';

const baseUrl = process.env.MAYHEN_BROWSER_BASE_URL;

test('Studio preserves a draft through template switch, publication and autosave', { skip: !baseUrl, timeout: 120000 }, async () => {
    const require = createRequire(import.meta.url);
    const { chromium } = require(process.env.MAYHEN_PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({ headless: true, executablePath: process.env.MAYHEN_CHROMIUM_PATH || undefined });
    const context = await browser.newContext();
    const page = await context.newPage();
    page.setDefaultTimeout(10000);
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    page.on('dialog', (dialog) => dialog.accept());
    const field = (key) => page.locator(`[data-sync-key="wedding.${key}"]`).first();
    try {
        await page.goto(`${baseUrl}/tao-thiep?template=giua-muon-ngan-vi-sao`, { waitUntil: 'domcontentloaded' });
        await field('groom_name').fill('Nguyễn Minh Đức');
        await field('bride_name').fill('Trần Ngọc An');
        await field('wedding_date').fill('2027-02-14');
        assert.deepEqual(await page.locator('[data-sync-key="wedding.wedding_date"]').evaluateAll((inputs) => inputs.map((input) => input.value)), ['2027-02-14', '2027-02-14']);
        await page.locator('[data-mayhen-step][data-section-id="story"]').click();
        await field('extra_data.story_content').fill('');
        await page.locator('[data-mayhen-step][data-section-id="timeline"]').click();
        await field('extra_data.timeline.1.title').fill('Khai tiệc riêng');
        await page.locator('[data-rail-tab="theme"]').click();
        await page.locator('[data-palette-id="sage-botanic"]').click();
        await page.locator('#mayhen_seal_monogram_input').fill('MA');
        await page.locator('[data-rail-tab="sections"]').click();
        await page.locator('.mayhen_editor_section_card[data-section-id="story"] [data-section-action="toggle-visibility"]').click();
        await page.reload({ waitUntil: 'domcontentloaded' });
        assert.equal(await field('groom_name').inputValue(), 'Nguyễn Minh Đức');
        assert.equal(await field('extra_data.story_content').inputValue(), '');
        assert.equal(await field('extra_data.timeline.1.title').inputValue(), 'Khai tiệc riêng');
        assert.match(await page.locator('.mayhen_editor_section_card[data-section-id="story"]').getAttribute('class'), /is-hidden/);

        const defaultImage = await field('extra_data.gallery.0').inputValue();
        await page.locator('#upload_gallery_photo_0').setInputFiles({ name: 'album.png', mimeType: 'image/png',
            buffer: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zf1sAAAAASUVORK5CYII=', 'base64') });
        await page.waitForFunction(() => document.querySelector('[data-sync-key="wedding.extra_data.gallery.0"]').value.startsWith('data:image'));
        const image = await field('extra_data.gallery.0').inputValue();
        await page.locator('#mayhen_btn_undo').click();
        assert.equal(await field('extra_data.gallery.0').inputValue(), defaultImage);
        await page.locator('#mayhen_btn_redo').click();
        assert.equal(await field('extra_data.gallery.0').inputValue(), image);
        await page.reload({ waitUntil: 'domcontentloaded' });
        assert.equal(await field('extra_data.gallery.0').inputValue(), image);
        await page.locator('[data-mayhen-action="open-template-modal"]').first().click();
        await page.locator('[data-template-switch][data-template-slug="hanh-phuc-mang-ten-chung-minh"]').click();
        await page.waitForURL((url) => url.searchParams.get('template') === 'hanh-phuc-mang-ten-chung-minh', { waitUntil: 'domcontentloaded' });
        await page.waitForFunction(() => document.querySelector('[data-sync-key="wedding.groom_name"]').value === 'Nguyễn Minh Đức');
        assert.equal(await field('extra_data.gallery.0').inputValue(), image);
        assert.equal(await field('extra_data.timeline.1.title').inputValue(), 'Khai tiệc riêng');
        assert.equal(await field('extra_data.story_content').inputValue(), '');
        const restoredConfig = JSON.parse(await page.locator('#invitation_custom_config').inputValue());
        assert.equal(restoredConfig.theme.palette_id, 'sage-botanic');
        assert.equal(restoredConfig.wax_seal.monogram, 'MA');
        assert.deepEqual(restoredConfig.sections_hidden, ['story']);

        await page.locator('[data-mayhen-action="trigger-submit"]').first().click();
        await page.waitForURL((url) => !url.pathname.includes('tao-thiep'), { waitUntil: 'domcontentloaded' });
        assert.equal((await page.locator('[data-sync-target="wedding_date_digits"]').first().innerText()).replace(/\s+/g, ''), '14.02.2027');
        assert.equal(await page.locator('[data-mayhen-section="story"]').evaluate((section) => getComputedStyle(section).display), 'none');
        const slug = new URL(page.url()).pathname.split('/').filter(Boolean).at(-1);
        await page.goto(`${baseUrl}/tao-thiep?edit=${encodeURIComponent(slug)}`, { waitUntil: 'domcontentloaded' });
        assert.equal(await field('wedding_date').inputValue(), '2027-02-14');
        assert.equal(await field('extra_data.story_content').inputValue(), '');
        assert.equal(await field('extra_data.timeline.1.title').inputValue(), 'Khai tiệc riêng');
        assert.equal(await field('extra_data.gallery.0').inputValue(), image);
        await field('groom_name').fill('Nguyễn Minh Quân');
        const autosave = await page.waitForResponse((response) => response.url().includes('/api/editor/autosave') && response.request().method() === 'POST');
        assert.equal(autosave.status(), 200, (await autosave.text()).slice(0, 400));
        await page.waitForFunction(() => document.querySelector('#mayhen_save_pill').dataset.status === 'saved');
        await page.reload({ waitUntil: 'domcontentloaded' });
        assert.equal(await field('groom_name').inputValue(), 'Nguyễn Minh Quân');
        assert.equal(await field('extra_data.timeline.1.title').inputValue(), 'Khai tiệc riêng');
        assert.equal(await field('extra_data.gallery.0').inputValue(), image);
        assert.equal(JSON.parse(await page.locator('#invitation_custom_config').inputValue()).theme.palette_id, 'sage-botanic');
        assert.deepEqual(errors, []);

        await context.setOffline(true);
        await field('groom_name').fill('Nguyễn Minh Khang');
        await page.waitForFunction(() => document.querySelector('#mayhen_save_pill').dataset.status === 'offline');
        const reconnected = page.waitForResponse((response) => response.url().includes('/api/editor/autosave') && response.request().method() === 'POST');
        await context.setOffline(false);
        assert.equal((await reconnected).status(), 200);
        await page.waitForFunction(() => document.querySelector('#mayhen_save_pill').dataset.status === 'saved');

        await page.evaluate(() => localStorage.removeItem(`mayhen_draft_${window.__MAYHEN_INVITATION_ID}`));
        const staleTab = await context.newPage();
        staleTab.on('dialog', (dialog) => dialog.accept());
        staleTab.on('pageerror', (error) => errors.push(error.message));
        await staleTab.goto(`${baseUrl}/tao-thiep?edit=${encodeURIComponent(slug)}`, { waitUntil: 'domcontentloaded' });
        await field('groom_name').fill('Nguyễn Minh Quân');
        assert.equal((await page.waitForResponse((response) => response.url().includes('/api/editor/autosave') && response.request().method() === 'POST')).status(), 200);
        await page.waitForFunction(() => document.querySelector('#mayhen_save_pill').dataset.status === 'saved');
        await staleTab.locator('[data-sync-key="wedding.groom_name"]').fill('Nguyễn Minh Phúc');
        assert.equal((await staleTab.waitForResponse((response) => response.url().includes('/api/editor/autosave') && response.request().method() === 'POST')).status(), 409);
        await staleTab.waitForFunction(() => document.querySelector('#mayhen_save_pill').dataset.status === 'conflict');
        await staleTab.locator('[data-mayhen-action="trigger-submit"]').first().click();
        assert.match(staleTab.url(), /tao-thiep/);
        await staleTab.reload({ waitUntil: 'domcontentloaded' });
        assert.equal(await staleTab.locator('#mayhen_editor_form').evaluate((form) => form.inert), true);
        await staleTab.locator('#mayhen_editor_recovery_continue').click();
        await staleTab.waitForFunction(() => {
            const form = document.querySelector('#mayhen_editor_form');
            return form && !form.inert;
        });
        assert.equal(await staleTab.locator('[data-sync-key="wedding.groom_name"]').inputValue(), 'Nguyễn Minh Quân');
        assert.equal(await staleTab.evaluate(() => Object.keys(localStorage).some((key) => key.includes('_recovery_'))), true);
        assert.deepEqual(errors, []);
    } catch (error) {
        throw new Error(`${error.message}\nURL: ${page.url()}\nBrowser errors: ${JSON.stringify(errors)}\nPage: ${(await page.locator('body').innerText()).slice(0,1400)}`, { cause: error });
    } finally {
        await browser.close();
    }
});

test('published invitations apply the Studio palette, monogram and section layout on every active template', { skip: !baseUrl, timeout: 120000 }, async () => {
    const require = createRequire(import.meta.url);
    const { chromium } = require(process.env.MAYHEN_PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({ headless: true, executablePath: process.env.MAYHEN_CHROMIUM_PATH || undefined });
    const backgrounds = {
        'giua-muon-ngan-vi-sao': '--dem',
        'hanh-phuc-mang-ten-chung-minh': '--mayhen-bg-cream',
        'ngay-tinh-yeu-dom-hoa': '--mayhen-invitation-bg',
        'tron-kiep-nay-ben-nhau': '--mayhen-invitation-bg-page',
    };
    const originals = {
        'giua-muon-ngan-vi-sao': '#030716',
        'hanh-phuc-mang-ten-chung-minh': '#f8f6f0',
        'ngay-tinh-yeu-dom-hoa': '#ffffff',
        'tron-kiep-nay-ben-nhau': '#FFFDF9',
    };
    try {
        for (const [slug, background] of Object.entries(backgrounds)) {
            const context = await browser.newContext();
            const page = await context.newPage();
            page.setDefaultTimeout(10000);
            const errors = [];
            page.on('pageerror', (error) => errors.push(error.message));
            page.on('dialog', (dialog) => dialog.accept());
            try {
                await page.goto(`${baseUrl}/tao-thiep?template=${slug}`, { waitUntil: 'domcontentloaded' });
                await page.locator('[data-sync-key="wedding.groom_name"]').fill('Nguyễn Minh Đức');
                await page.locator('[data-sync-key="wedding.bride_name"]').fill('Trần Ngọc An');
                await page.locator('[data-rail-tab="theme"]').click();
                await page.locator('[data-palette-id="warm-terracotta"]').click();
                const previewColorMatches = ([property, expected]) => {
                    const frame = document.querySelector('#mayhen_editor_preview_iframe');
                    return frame?.contentDocument?.documentElement
                        && frame.contentWindow.getComputedStyle(frame.contentDocument.documentElement).getPropertyValue(property).trim() === expected;
                };
                await page.waitForFunction(previewColorMatches, [background, '#FAF4EF']);
                await page.locator('#mayhen_btn_undo').click();
                await page.waitForFunction(previewColorMatches, [background, originals[slug]]);
                await page.locator('#mayhen_btn_redo').click();
                await page.waitForFunction(previewColorMatches, [background, '#FAF4EF']);
                await page.locator('#mayhen_seal_monogram_input').fill('QA');
                await page.locator('[data-rail-tab="sections"]').click();
                for (const section of ['parents', 'ending']) {
                    await page.locator(`.mayhen_editor_section_card[data-section-id="${section}"] [data-section-action="toggle-visibility"]`).click();
                }
                await page.locator('.mayhen_editor_section_card[data-section-id="timeline"] [data-section-action="move-up"]').click();
                await page.locator('[data-mayhen-action="trigger-submit"]').first().click();
                await page.waitForURL((url) => url.pathname.startsWith('/thiep/'), { waitUntil: 'domcontentloaded' });
                assert.equal(await page.evaluate((property) => getComputedStyle(document.documentElement).getPropertyValue(property).trim(), background), '#FAF4EF', slug);
                assert.equal(await page.locator('[data-mayhen-wax-monogram]').first().textContent(), 'QA', slug);
                const layout = await page.evaluate(() => {
                    const nodes = Array.from(document.querySelectorAll('[data-mayhen-section]'));
                    const aliases = (node) => node.dataset.mayhenSection.split(',');
                    return {
                        familyHidden: nodes.filter((node) => aliases(node).some((key) => ['parents', 'groom_family', 'bride_family'].includes(key)))
                            .every((node) => getComputedStyle(node).display === 'none' || node.closest('[data-mayhen-section]')?.style.display === 'none'),
                        endingHidden: nodes.filter((node) => aliases(node).some((key) => ['ending', 'signature', 'interactive', 'rsvp', 'wishes'].includes(key)))
                            .every((node) => getComputedStyle(node).display === 'none'),
                        timelineBeforeEvents: nodes.findIndex((node) => aliases(node).includes('timeline')) < nodes.findIndex((node) => aliases(node).some((key) => ['events', 'wedding_event'].includes(key))),
                    };
                });
                assert.deepEqual(layout, { familyHidden: true, endingHidden: true, timelineBeforeEvents: true }, slug);
                assert.deepEqual(errors, [], slug);
            } catch (error) {
                throw new Error(`${slug}: ${error.message}\nURL: ${page.url()}\nBrowser errors: ${JSON.stringify(errors)}`, { cause: error });
            } finally {
                await context.close();
            }
        }
    } finally {
        await browser.close();
    }
});
