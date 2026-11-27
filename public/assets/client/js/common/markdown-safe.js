/**
 * MAYHEN.VN — SAFE AI MARKDOWN -> HTML FORMATTER
 * Chuyển đổi an toàn câu trả lời AI & văn bản luận giải từ Markdown sang HTML sạch (Zero-Trust XSS-safe).
 * Đồng bộ 1:1 quy tắc với App\Services\Intelligence\SafeAiMarkdownFormatter.
 */

const MAX_INLINE_DEPTH = 3;

export function escapeMarkdownHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function isSafeMarkdownUrl(url) {
    const clean = String(url ?? '').trim();
    if (!clean) return false;
    if (clean.startsWith('/') && !clean.startsWith('//') && !clean.startsWith('/\\')) {
        return /^\/[a-zA-Z0-9\-_/?=&%#.]*$/.test(clean);
    }
    return /^https:\/\/(?:www\.)?mayhen\.vn(?:\/|$|\?|#)/i.test(clean);
}

function matchUnorderedItem(line) {
    const m = String(line ?? '').match(/^[ \t]*(?:[-*+•◦▪])\s+(.+)$/);
    return m ? m[1] : null;
}

function matchOrderedItem(line) {
    const m = String(line ?? '').match(/^[ \t]*\d{1,3}[.)]\s+(.+)$/);
    return m ? m[1] : null;
}

function parseInlineMarkdown(text, depth = 0) {
    const str = String(text ?? '');
    if (!str) return '';
    if (depth >= MAX_INLINE_DEPTH) {
        return escapeMarkdownHtml(str);
    }

    const tokenRegex = /`([^`\n]+)`|\[([^\]\n]+)\]\(([^()\s]+(?:\([^()\s]*\)[^()\s]*)*)\)|\*\*\*([^\s*\n](?:[^\n]*?[^\s*\n])?)\*\*\*|\*\*([^\s*\n](?:[^\n]*?[^\s*\n])?)\*\*|__([^\s_\n](?:[^\n]*?[^\s_\n])?)__|(?<![\p{L}\p{N}])\*(?!\s)([^*\n]+?)(?<!\s)\*(?!\*)(?![\p{L}\p{N}])/gu;

    let out = '';
    let lastIndex = 0;
    let match;

    while ((match = tokenRegex.exec(str)) !== null) {
        if (match.index > lastIndex) {
            out += escapeMarkdownHtml(str.slice(lastIndex, match.index));
        }

        if (match[1] !== undefined) {
            out += `<code class="mayhen_md_code">${escapeMarkdownHtml(match[1])}</code>`;
        } else if (match[2] !== undefined && match[3] !== undefined) {
            const label = match[2].trim();
            const href = match[3].trim();
            if (isSafeMarkdownUrl(href)) {
                out += `<a class="mayhen_md_link" href="${escapeMarkdownHtml(href)}" target="_blank" rel="noopener noreferrer">${parseInlineMarkdown(label, depth + 1)}</a>`;
            } else {
                out += parseInlineMarkdown(label, depth + 1);
            }
        } else if (match[4] !== undefined) {
            out += `<strong><em>${parseInlineMarkdown(match[4], depth + 1)}</em></strong>`;
        } else if (match[5] !== undefined) {
            out += `<strong>${parseInlineMarkdown(match[5], depth + 1)}</strong>`;
        } else if (match[6] !== undefined) {
            out += `<strong>${parseInlineMarkdown(match[6], depth + 1)}</strong>`;
        } else if (match[7] !== undefined) {
            out += `<em>${parseInlineMarkdown(match[7], depth + 1)}</em>`;
        } else {
            out += escapeMarkdownHtml(match[0]);
        }

        lastIndex = tokenRegex.lastIndex;
    }

    if (lastIndex < str.length) {
        out += escapeMarkdownHtml(str.slice(lastIndex));
    }

    return out;
}

/**
 * Chuyển đổi văn bản Markdown thành khối HTML hoàn chỉnh (<p>, <h4>, <h5>, <ul>, <ol>, .mayhen_md_subline, <blockquote>, <pre>).
 */
export function formatSafeAiMarkdown(rawText) {
    const normalized = String(rawText ?? '').replace(/\r\n?/g, '\n').trim();
    if (!normalized) return '';

    const lines = normalized.split('\n');
    const blocks = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];
        const trimmed = line.trim();

        if (trimmed === '') {
            i++;
            continue;
        }

        // 1. Code fence ```...```
        if (/^```/.test(trimmed)) {
            i++;
            const codeLines = [];
            while (i < lines.length && !/^```\s*$/.test(lines[i].trim())) {
                codeLines.push(lines[i]);
                i++;
            }
            if (i < lines.length) i++;
            blocks.push(`<pre class="mayhen_md_pre"><code>${escapeMarkdownHtml(codeLines.join('\n'))}</code></pre>`);
            continue;
        }

        // 2. Đường kẻ phân tách --- / *** / ___
        if (/^(?:[-*_]){3,}\s*$/.test(trimmed)) {
            blocks.push('<hr class="mayhen_md_hr">');
            i++;
            continue;
        }

        // 3. Tiêu đề #, ##, ### -> h4 | ####+ -> h5
        const headingMatch = trimmed.match(/^(#{1,6})\s+(.+)$/);
        if (headingMatch) {
            const level = headingMatch[1].length;
            const tag = level <= 3 ? 'h4' : 'h5';
            const cls = level <= 3 ? 'mayhen_md_heading' : 'mayhen_md_subheading';
            blocks.push(`<${tag} class="${cls}">${parseInlineMarkdown(headingMatch[2].trim())}</${tag}>`);
            i++;
            continue;
        }

        // 4. Trích dẫn > ...
        const quoteMatch = trimmed.match(/^>\s?(.*)$/);
        if (quoteMatch) {
            const quoteLines = [quoteMatch[1].trim()];
            i++;
            while (i < lines.length) {
                const nextQuote = lines[i].trim().match(/^>\s?(.*)$/);
                if (!nextQuote) break;
                quoteLines.push(nextQuote[1].trim());
                i++;
            }
            const rendered = quoteLines.filter(Boolean).map((qLine) => parseInlineMarkdown(qLine));
            if (rendered.length > 0) {
                blocks.push(`<blockquote class="mayhen_md_quote">${rendered.join('<br>')}</blockquote>`);
            }
            continue;
        }

        // 5. Danh sách không thứ tự / có thứ tự
        const isUnordered = matchUnorderedItem(line) !== null;
        const isOrdered = !isUnordered && matchOrderedItem(line) !== null;

        if (isUnordered || isOrdered) {
            const listTag = isUnordered ? 'ul' : 'ol';
            const itemsHtml = [];

            while (i < lines.length) {
                const itemText = isUnordered ? matchUnorderedItem(lines[i]) : matchOrderedItem(lines[i]);

                if (itemText === null) {
                    const nextItem = lines[i + 1];
                    if (
                        lines[i].trim() === ''
                        && nextItem !== undefined
                        && ((isUnordered && matchUnorderedItem(nextItem) !== null)
                            || (isOrdered && matchOrderedItem(nextItem) !== null))
                    ) {
                        i++;
                        continue;
                    }
                    break;
                }

                i++;
                let subHtml = '';
                while (i < lines.length) {
                    const subMatch = lines[i].match(/^[ \t]*↳\s*(.+)$/);
                    if (!subMatch) break;
                    subHtml += `<div class="mayhen_md_subline"><span class="mayhen_md_sub_arrow" aria-hidden="true">↳</span><span>${parseInlineMarkdown(subMatch[1].trim())}</span></div>`;
                    i++;
                }

                itemsHtml.push(`<li>${parseInlineMarkdown(itemText.trim())}${subHtml}</li>`);
            }

            const listCls = listTag === 'ol' ? 'mayhen_md_list mayhen_md_list--ordered' : 'mayhen_md_list';
            blocks.push(`<${listTag} class="${listCls}">${itemsHtml.join('')}</${listTag}>`);
            continue;
        }

        // 6. Dòng phụ chú độc lập "↳ ..."
        const standaloneSub = line.match(/^[ \t]*↳\s*(.+)$/);
        if (standaloneSub) {
            blocks.push(`<div class="mayhen_md_subline"><span class="mayhen_md_sub_arrow" aria-hidden="true">↳</span><span>${parseInlineMarkdown(standaloneSub[1].trim())}</span></div>`);
            i++;
            continue;
        }

        // 7. Đoạn văn thường
        const paraLines = [];
        while (i < lines.length) {
            const curr = lines[i];
            const currTrim = curr.trim();
            if (
                currTrim === ''
                || /^```/.test(currTrim)
                || /^(?:[-*_]){3,}\s*$/.test(currTrim)
                || /^(#{1,6})\s+(.+)$/.test(currTrim)
                || /^>\s?(.*)$/.test(currTrim)
                || /^[ \t]*↳\s*(.+)$/.test(curr)
                || matchUnorderedItem(curr) !== null
                || matchOrderedItem(curr) !== null
            ) {
                break;
            }
            paraLines.push(parseInlineMarkdown(currTrim));
            i++;
        }

        if (paraLines.length > 0) {
            blocks.push(`<p class="mayhen_md_p">${paraLines.join('<br>')}</p>`);
        }
    }

    return blocks.join('');
}

/**
 * Chuyển đổi chỉ các thẻ inline (**bold**, *italic*, `code`, link an toàn) cho tiêu đề / bullet ngắn.
 */
export function formatSafeAiInline(rawText) {
    const normalized = String(rawText ?? '').replace(/\r\n?/g, '\n').trim();
    if (!normalized) return '';
    return normalized
        .split('\n')
        .map((line) => parseInlineMarkdown(line.trim()))
        .join('<br>');
}
