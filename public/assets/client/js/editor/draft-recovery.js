import { EditorStore } from './store.js';

export const isWeddingImageField = (key) => /photo|cover|image|gallery|album/i.test(key);

export const isUploadedImage = (value) => typeof value === 'string' && (
    value.startsWith('data:image/') || value.startsWith('/storage/') || value.startsWith('/uploads/')
    || /^https?:\/\/[^/]+\/(storage|uploads)\//.test(value)
);

export function mirrorWeddingInput(form, source) {
    const key = source.getAttribute('data-sync-key');
    if (!key) return;
    Array.from(form?.querySelectorAll('[data-sync-key]') || []).forEach((input) => {
        if (input !== source && input.getAttribute('data-sync-key') === key && input.value !== source.value) {
            input.value = source.value;
        }
    });
}

/** Apply a draft to the form and build the same nested wedding payload for autosave. */
export function applyWeddingDraft(form, draft, baseWedding = {}, doc = globalThis.document, options = {}) {
    const wedding = EditorStore.normalizeWeddingFields(baseWedding);
    const restoredKeys = new Set();
    const inputs = new Map();
    Array.from(form?.querySelectorAll('[data-sync-key]') || []).forEach((input) => {
        const key = input.getAttribute('data-sync-key');
        if (!inputs.has(key)) inputs.set(key, []);
        inputs.get(key).push(input);
    });

    const fields = EditorStore.flattenWeddingFields(draft);
    const snapshotKeys = new Set(Object.keys(fields).map((key) => `wedding.${EditorStore.canonicalWeddingPath(key)}`));
    if (options.authoritative) {
        inputs.forEach((matchingInputs, key) => {
            if (snapshotKeys.has(key)) return;
            matchingInputs.forEach((input) => {
                if (input.getAttribute('data-mayhen-preserved-field') === 'true') input.remove();
            });
        });
    }
    Object.entries(fields).forEach(([key, value]) => {
        if (!options.authoritative && isWeddingImageField(key) && !isUploadedImage(value)) return;
        const fieldKey = `wedding.${EditorStore.canonicalWeddingPath(key)}`;
        let matchingInputs = inputs.get(fieldKey) || inputs.get(key) || [];
        if (!matchingInputs.length && form) {
            const input = doc.createElement('input');
            input.type = 'hidden';
            input.name = fieldKey.split('.').map((part, index) => index ? `[${part}]` : part).join('');
            input.setAttribute('data-sync-key', fieldKey);
            input.setAttribute('data-mayhen-preserved-field', 'true');
            form.appendChild(input);
            matchingInputs = [input];
            inputs.set(fieldKey, matchingInputs);
        }
        const formValue = value === null ? '' : String(value);
        matchingInputs.forEach((input) => {
            input.value = formValue;
            restoredKeys.add(input.getAttribute('data-sync-key'));
            const thumb = input.id ? doc.getElementById(`preview_thumb_${input.id}`) : null;
            if (thumb && (options.authoritative || isUploadedImage(formValue))) {
                if (!formValue) {
                    thumb.removeAttribute('src');
                } else if (options.templateSlug && !/^(https?:|data:|blob:|\/)/.test(formValue)) {
                    thumb.src = `/mau-thiep/${options.templateSlug}/assets/${formValue.replace(/^assets\//, '')}`;
                } else {
                    thumb.src = formValue;
                }
            }
        });
        EditorStore.setByPath(wedding, fieldKey.replace(/^wedding\./, ''), formValue);
    });
    return { wedding, restoredKeys };
}
