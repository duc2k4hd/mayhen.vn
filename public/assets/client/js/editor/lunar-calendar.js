/**
 * Pure in-process Vietnamese Lunar Calendar converter (Hồ Ngọc Đức — GMT+7)
 * & field classification helpers. Zero DOM dependencies.
 */

function INT(d) {
    return Math.floor(d);
}

function jdFromDate(d, m, y) {
    const a = INT((14 - m) / 12);
    const y1 = y + 4800 - a;
    const m1 = m + 12 * a - 3;
    return d + INT((153 * m1 + 2) / 5) + 365 * y1 + INT(y1 / 4) - INT(y1 / 100) + INT(y1 / 400) - 32045;
}

function getNewMoonDay(k, tz) {
    const T = k / 1236.85;
    const T2 = T * T;
    const T3 = T2 * T;
    const dr = Math.PI / 180;
    const Jd1 = 2415020.75933 + 29.53058868 * k + 0.0001178 * T2 - 0.000000155 * T3;
    const M = 359.2242 + 29.10535608 * k - 0.0000333 * T2 - 0.00000347 * T3;
    const Mprime = 306.0253 + 385.81691806 * k + 0.0107306 * T2 + 0.00001236 * T3;
    const F = 21.2964 + 390.67050646 * k - 0.0016528 * T2 - 0.00000239 * T3;
    const C1 = (0.1734 - 0.000393 * T) * Math.sin(M * dr) + 0.0021 * Math.sin(2 * dr * M)
        - 0.4068 * Math.sin(Mprime * dr) + 0.0161 * Math.sin(2 * dr * Mprime)
        - 0.0004 * Math.sin(3 * dr * Mprime) + 0.0104 * Math.sin(2 * dr * F)
        - 0.0051 * Math.sin(dr * (M + Mprime)) - 0.0074 * Math.sin(dr * (M - Mprime))
        + 0.0004 * Math.sin(dr * (2 * F + M)) - 0.0004 * Math.sin(dr * (2 * F - M))
        - 0.0006 * Math.sin(dr * (2 * F + Mprime)) + 0.001 * Math.sin(dr * (2 * F - Mprime))
        + 0.0005 * Math.sin(dr * (M + 2 * Mprime));
    return INT(Jd1 + C1 + 0.5 + tz / 24);
}

function getSunLongitude(jdn, tz) {
    const T = (jdn - 2451545.0 + 0.5 - tz / 24) / 36525;
    const dr = Math.PI / 180;
    const M = 357.5291 + 35999.0503 * T - 0.0001559 * T * T - 0.00000048 * T * T * T;
    const L0 = 280.46645 + 36000.76983 * T + 0.0003032 * T * T;
    const DL = (1.9146 - 0.004817 * T - 0.000014 * T * T) * Math.sin(dr * M)
        + (0.019993 - 0.000101 * T) * Math.sin(2 * dr * M) + 0.00029 * Math.sin(3 * dr * M);
    const L = (L0 + DL) * dr;
    return INT((L - Math.PI * 2 * INT(L / (Math.PI * 2))) / (Math.PI / 6));
}

function getLunarMonth11(y, tz) {
    const off = jdFromDate(31, 12, y) - 2415021;
    const k = INT(off / 29.530588853);
    const nm = getNewMoonDay(k, tz);
    return getSunLongitude(nm, tz) >= 9 ? getNewMoonDay(k - 1, tz) : nm;
}

function getLeapOffset(a11, tz) {
    const k = INT((a11 - 2415021.076998695) / 29.530588853 + 0.5);
    let last = 0;
    let i = 1;
    let arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
    do {
        last = arc;
        i++;
        arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
    } while (arc !== last && i < 14);
    return i - 1;
}

const CAN_LIST = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
const CHI_LIST = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

/**
 * Convert a YYYY-MM-DD solar date string to a formatted Vietnamese lunar date string.
 */
export function calcVietnameseLunar(solarDateStr) {
    if (!solarDateStr) return '';
    const parts = solarDateStr.toString().trim().split('-');
    if (parts.length !== 3) return '';
    const yy = parseInt(parts[0], 10);
    const mm = parseInt(parts[1], 10);
    const dd = parseInt(parts[2], 10);
    if (Number.isNaN(yy) || Number.isNaN(mm) || Number.isNaN(dd)) return '';

    const tz = 7;
    const dayNumber = jdFromDate(dd, mm, yy);
    const k = INT((dayNumber - 2415021.076998695) / 29.530588853);
    let monthStart = getNewMoonDay(k + 1, tz);
    if (monthStart > dayNumber) {
        monthStart = getNewMoonDay(k, tz);
    }
    let a11 = getLunarMonth11(yy, tz);
    let b11 = a11;
    let lunarYear = yy;
    if (a11 >= monthStart) {
        lunarYear = yy;
        a11 = getLunarMonth11(yy - 1, tz);
    } else {
        lunarYear = yy + 1;
        b11 = getLunarMonth11(yy + 1, tz);
    }
    const lunarDay = dayNumber - monthStart + 1;
    const diff = INT((monthStart - a11) / 29);
    let lunarLeap = 0;
    let lunarMonth = diff + 11;
    if (b11 - a11 > 365) {
        const leapDiff = getLeapOffset(a11, tz);
        if (diff >= leapDiff) {
            lunarMonth = diff + 10;
            if (diff === leapDiff) lunarLeap = 1;
        }
    }
    if (lunarMonth > 12) lunarMonth -= 12;
    if (lunarMonth >= 11 && diff < 4) lunarYear -= 1;

    const canYear = CAN_LIST[(lunarYear + 6) % 10];
    const chiYear = CHI_LIST[(lunarYear + 8) % 12];
    const leapText = lunarLeap === 1 ? ' nhuận' : '';

    const dStr = lunarDay < 10 ? `0${lunarDay}` : `${lunarDay}`;
    const mStr = lunarMonth < 10 ? `0${lunarMonth}` : `${lunarMonth}`;

    return `Nhằm ngày ${dStr} tháng ${mStr}${leapText} năm ${canYear} ${chiYear}`;
}

/**
 * Check whether a canonical schema key represents an image or album slot.
 */
export function isImageKey(key) {
    const lower = String(key || '').toLowerCase();
    return lower.includes('photo')
        || lower.includes('cover')
        || lower.includes('image')
        || lower.includes('gallery')
        || lower.includes('album');
}
