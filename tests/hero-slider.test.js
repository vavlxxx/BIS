const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function fixture(reduced = false) {
    const element = () => ({ attrs: {}, events: {}, classList: { toggle() {} }, setAttribute(k,v) { this.attrs[k]=v; }, addEventListener(k,v) { this.events[k]=v; } });
    const screens = [element(),element(),element()];
    const images = screens.map(element), dots = screens.map(element);
    const root=element();
    const selectors = {'[data-hero-screen]':screens,'.hero-slide':images,'[data-hero-dot]':dots};
    root.querySelectorAll = s => selectors[s];
    root.contains = () => false;
    let tick;
    const doc = { hidden:false, querySelector:()=>root, addEventListener(k,fn) { if(k==='DOMContentLoaded') this.start=fn; else this[k]=fn; } };
    vm.runInNewContext(fs.readFileSync('wp-theme/assets/js/slider.js','utf8'), {document:doc,window:{matchMedia:()=>({matches:reduced,addEventListener(){}})},setInterval(fn){tick=fn;return 1;},clearInterval(){tick=null;},setTimeout:fn=>fn()});
    doc.start();
    return {screens,dots,root,doc,tick:()=>tick};
}
test('hero navigation wraps, selects dots and hides inactive screen controls',()=>{
    const f=fixture();
    assert.equal(f.screens[1].inert,true);
    f.dots[2].events.click();
    assert.equal(f.screens[2].inert,false);
    f.tick()();
    assert.equal(f.screens[0].attrs['aria-hidden'],'false');
    f.dots[1].events.click();
    assert.equal(f.dots[1].attrs['aria-pressed'],'true');
    assert.equal(f.screens[0].inert,true);
});
test('hero autoplay pauses for pointer, hidden page and reduced motion without extra controls',()=>{
    const f=fixture();
    f.tick()();assert.equal(f.screens[1].inert,false);
    f.root.events.mouseenter();assert.equal(f.tick(),null);
    f.root.events.mouseleave();assert.equal(typeof f.tick(),'function');
    f.doc.hidden=true;f.doc.visibilitychange();assert.equal(f.tick(),null);
    assert.equal(fixture(true).tick(),null);
});
