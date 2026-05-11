// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Section tabs controller for block_coursecardsuems.
 *
 * Replaces the native <details> collapse UI with three intercambiable
 * tab panels (Open / Coming soon / Closed). Active tab is persisted in
 * localStorage so it survives page reloads within the same browser.
 *
 * @module     block_coursecardsuems/section_tabs
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const STORAGE_KEY = 'block_coursecardsuems_active_tab';
const SEL_ROOT    = '[data-section-tabs]';
const SEL_TAB     = '[role="tab"]';
const SEL_PANEL   = '[role="tabpanel"]';

/**
 * Activates the tab matching key and hides all others.
 *
 * @param {HTMLElement} root
 * @param {string} key
 */
const activate = (root, key) => {
    const tabs   = root.querySelectorAll(SEL_TAB);
    const panels = root.querySelectorAll(SEL_PANEL);

    let found = false;
    tabs.forEach(tab => {
        const active = tab.dataset.key === key;
        if (active) {
            found = true;
        }
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', String(active));
        tab.setAttribute('tabindex', active ? '0' : '-1');
    });

    // Fallback: if stored key no longer exists, activate first tab.
    if (!found && tabs.length) {
        activate(root, tabs[0].dataset.key);
        return;
    }

    panels.forEach(panel => {
        const active = panel.dataset.key === key;
        panel.toggleAttribute('hidden', !active);
    });

    try {
        localStorage.setItem(STORAGE_KEY, key);
    } catch (_) {
        // Private browsing or storage quota — silently ignore.
    }
};

/**
 * Handles keyboard navigation (←/→ arrows) within the tablist.
 *
 * @param {HTMLElement} root
 * @param {KeyboardEvent} e
 */
const handleKeydown = (root, e) => {
    const tabs = [...root.querySelectorAll(SEL_TAB)];
    const idx  = tabs.indexOf(e.target);
    if (idx === -1) {
        return;
    }

    let next = -1;
    if (e.key === 'ArrowRight') {
        next = (idx + 1) % tabs.length;
    } else if (e.key === 'ArrowLeft') {
        next = (idx - 1 + tabs.length) % tabs.length;
    } else if (e.key === 'Home') {
        next = 0;
    } else if (e.key === 'End') {
        next = tabs.length - 1;
    }

    if (next !== -1) {
        e.preventDefault();
        activate(root, tabs[next].dataset.key);
        tabs[next].focus();
    }
};

/**
 * Initialises section tabs for all matching roots on the page.
 *
 * Called by $PAGE->requires->js_call_amd('block_coursecardsuems/section_tabs', 'init').
 */
export const init = () => {
    document.querySelectorAll(SEL_ROOT).forEach(root => {
        const defaultKey = root.dataset.activeKey ?? 'open';
        let initialKey = defaultKey;

        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                initialKey = saved;
            }
        } catch (_) {
            // Ignore.
        }

        activate(root, initialKey);

        root.querySelectorAll(SEL_TAB).forEach(tab => {
            tab.addEventListener('click', () => activate(root, tab.dataset.key));
            tab.addEventListener('keydown', e => handleKeydown(root, e));
        });
    });
};
