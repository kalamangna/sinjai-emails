/**
 * utils.js — Shared utility functions for the email management system.
 * Loaded globally before page-specific scripts.
 */

/**
 * Generate a password based on employee name and NIP.
 *
 * Format: <5-char name>@<2-digit suffix>#
 *
 * Suffix priority:
 *   1. NIP[3–4]  (2-digit birth year, default)
 *   2. NIP[7–8]  (2-digit birth date, when useAltNipPart = true)
 *   3. Random 2-digit number (when NIP is unavailable)
 *
 * Short names (< 5 chars) are repeated until they reach 5 characters.
 *
 * @param {string}  name           - Employee full name
 * @param {string}  nip            - NIP string
 * @param {boolean} useAltNipPart  - If true, use NIP[7–8] instead of NIP[3–4]
 * @returns {string}
 */
function generatePassword(name, nip, useAltNipPart = false) {
    let suffix;
    if (nip && nip.length >= 8) {
        suffix = useAltNipPart ? nip.substring(6, 8) : nip.substring(2, 4);
    } else if (nip && nip.length >= 4) {
        suffix = nip.substring(2, 4);
    } else {
        suffix = String(Math.floor(Math.random() * 90) + 10);
    }

    let baseName = (name || '').replace(/\s+/g, '').toLowerCase();
    let namePart = baseName;
    if (namePart.length > 0 && namePart.length < 5) {
        while (namePart.length < 5) namePart += baseName;
    }
    namePart = namePart.substring(0, 5);

    if (!namePart) return `@${suffix}#`;
    return namePart.charAt(0).toUpperCase() + namePart.slice(1) + `@${suffix}#`;
}
