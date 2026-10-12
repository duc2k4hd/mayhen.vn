/**
 * MAYHEN.VN — DOM UTILITY MODULE
 * Native Vanilla ES Modules
 */

export const $ = (selector, context = document) => {
    return context.querySelector(selector);
};

export const $$ = (selector, context = document) => {
    return Array.from(context.querySelectorAll(selector));
};

export const on = (element, event, handler, options = {}) => {
    if (!element) return;
    element.addEventListener(event, handler, options);
};

export const toggleClass = (element, className, force) => {
    if (!element) return;
    element.classList.toggle(className, force);
};
