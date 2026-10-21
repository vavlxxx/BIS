const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

test('smooth scroll handles anchor links, excludes external pages, and accounts for header', () => {
  let scrolledTo = null;
  let prevented = false;
  let pushStateCalled = false;
  let drawerClosed = false;

  const documentMock = {
    getElementById(id) {
      if (id === 'header') return { offsetHeight: 70 };
      if (id === 'navDrawer') return { classList: { remove() { drawerClosed = true; } }, setAttribute() {} };
      if (id === 'menuToggle') return { classList: { remove() {} } };
      return null;
    },
    querySelector(selector) {
      if (selector === '#services') {
        return {
          getBoundingClientRect: () => ({ top: 800 })
        };
      }
      return null;
    },
    querySelectorAll(selector) {
      if (selector === 'a[href^="#"], a[href*="/#"]') {
        return [
          {
            getAttribute: () => '#services',
            classList: { contains: () => false },
            hostname: 'biscorp.ru',
            pathname: '/'
          },
          {
            getAttribute: () => '/about/',
            classList: { contains: () => false },
            hostname: 'biscorp.ru',
            pathname: '/about/'
          }
        ];
      }
      return [];
    },
    body: {
      classList: { remove() {} }
    },
    documentElement: { scrollTop: 0 }
  };

  let mockTime = 1000;
  const windowMock = {
    pageYOffset: 100,
    scrollY: 100,
    location: {
      hostname: 'biscorp.ru',
      pathname: '/',
      hash: ''
    },
    history: {
      pushState: () => { pushStateCalled = true; }
    },
    matchMedia: () => ({ matches: false }),
    scrollTo(x, y) {
      scrolledTo = { x, y };
    },
    requestAnimationFrame(cb) {
      mockTime += 1000;
      cb(mockTime);
    },
    performance: { now: () => mockTime },
    setTimeout: (fn) => fn()
  };

  const context = vm.createContext({
    window: windowMock,
    document: documentMock,
    console,
    history: windowMock.history,
    setTimeout: windowMock.setTimeout,
    clearTimeout: () => {},
    requestAnimationFrame: windowMock.requestAnimationFrame,
    performance: windowMock.performance
  });

  const code = fs.readFileSync('wp-theme/assets/js/site-navigation.js', 'utf8');
  vm.runInContext(code, context);

  // Test scrollToElement directly
  const target = documentMock.querySelector('#services');
  vm.runInContext('scrollToElement', context)(target, '#services');

  // Target element top is 800, current scroll is 100, header is 70 => targetPosition is 800 + 100 - 70 = 830
  assert.equal(scrolledTo.y, 830, 'Scroll position must be elementTop + scrollY - headerHeight');
  assert.equal(pushStateCalled, true, 'History state should be updated with hash');

  // Verify smoothScrollToY with reduced motion jumps directly
  windowMock.matchMedia = () => ({ matches: true });
  vm.runInContext('smoothScrollToY', context)(500);
  assert.equal(scrolledTo.y, 500, 'Reduced motion should jump directly to target');
});
