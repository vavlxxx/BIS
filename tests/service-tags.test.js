const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(apiError = null) {
  let pasteHandler;
  let assigned = [7];
  let locked = false;
  const calls = [];
  const errors = [];
  const title = { textContent: 'Теги услуг' };
  const panel = { querySelector: () => title };
  const wp = {
    apiFetch: async (options) => {
      calls.push(options);
      if (apiError) throw apiError;
      return { ids: [8, 9], count: 2 };
    },
    data: {
      select: () => ({
        getCurrentPostId: () => 1045,
        getEditedPostAttribute: () => assigned,
      }),
      dispatch: (store) => store === 'core/editor' ? {
        lockPostSaving: () => { locked = true; },
        unlockPostSaving: () => { locked = false; },
        editPost: (changes) => { assigned = changes.bis_service_tag; },
      } : {
        createSuccessNotice: () => {},
        createErrorNotice: (message) => { errors.push(message); },
      },
    },
  };
  const document = { addEventListener: (name, handler) => {
    if (name === 'paste') pasteHandler = handler;
  } };
  const script = fs.readFileSync(path.join(__dirname, '../wp-theme/assets/js/admin-service-tag-import.js'), 'utf8');
  vm.runInNewContext(script, { window: { wp }, document });
  return {
    calls,
    errors,
    get assigned() { return assigned; },
    get locked() { return locked; },
    paste: (text, inTags = true) => {
      let prevented = false;
      const event = {
        target: { tagName: 'INPUT', closest: () => inTags ? panel : null },
        clipboardData: { getData: () => text },
        preventDefault: () => { prevented = true; },
        stopPropagation: () => {},
      };
      const operation = pasteHandler(event);
      return { operation, prevented };
    },
  };
}

test('customer bulk paste uses one request and assigns every returned tag', async () => {
  const app = setup();
  const paste = app.paste('проектирование вентиляции, проектирование вентиляции бассейна');
  assert.equal(paste.prevented, true);
  assert.equal(app.locked, true);
  await paste.operation;
  assert.equal(app.calls.length, 1);
  assert.equal(app.calls[0].path, '/bis/v1/service-tags/import');
  assert.equal(app.calls[0].data.post_id, 1045);
  assert.deepEqual(Array.from(app.assigned), [7, 8, 9]);
  assert.equal(app.locked, false);
});

test('pasting outside service tags retains the normal editor action', async () => {
  const app = setup();
  const paste = app.paste('один, два', false);
  await paste.operation;
  assert.equal(paste.prevented, false);
  assert.equal(app.calls.length, 0);
});

test('failed import unlocks saving and leaves assigned tags intact', async () => {
  const app = setup(new Error('Server error'));
  const paste = app.paste('один, два');
  await paste.operation;
  assert.equal(paste.prevented, true);
  assert.deepEqual(Array.from(app.assigned), [7]);
  assert.equal(app.locked, false);
  assert.match(app.errors[0], /Server error/);
});
