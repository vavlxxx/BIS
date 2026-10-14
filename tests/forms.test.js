const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const context = vm.createContext({window:{bisSiteConfig:{captchaEnabled:false}}, document:{createElement:()=>({}),head:{appendChild(){}}, addEventListener(){}}, console, setTimeout, clearTimeout});
vm.runInContext(fs.readFileSync('wp-theme/assets/js/site-forms.js','utf8'),context);
test('accepts a complete masked phone and rejects short or long numbers', () => {
  assert.equal(vm.runInContext("isValidRussianPhone('+7 (926) 438-07-70')",context),true);
  for (const phone of ['+7 (926) 438-07','+7 (926) 438-07-700','abc','']) {
    context.phone = phone;
    assert.equal(vm.runInContext('isValidRussianPhone(phone)',context),false);
  }
});
test('mask does not turn an overlong pasted number into a valid one', () => {
  assert.equal(vm.runInContext("isValidRussianPhone(formatRussianPhone('792643807701'))",context),false);
});
test('captcha-off does not try to initialize or validate a widget', () => {
  assert.equal(vm.runInContext('validateHCaptcha({})',context),true);
});
