const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const calculators = require('../wp-theme/assets/js/calculator-core.js');

test('smoke extraction requires a real fan-curve flow rather than project flow', () => {
  const sample = {
    projectFlow: 27500, fanPressure: 250, smokeTemp: 760,
    fanGasTemp: 619, roomTemp: 20, fanElevation: 10,
    intakeElevation: -1, actualFlow: 12092,
    sections: [{ floor: 1, length: 3, width: 1, height: 1,
      localResistance: 0.4, friction: 0.016, valveWidth: 0.8,
      valveHeight: 0.5, valveAirResistance: 10000 }]
  };
  const pressure = calculators.calculateFanCurvePressure(sample);
  assert.ok(Math.abs(pressure.curvePressure - 92.9834794) < 0.0001);
  assert.ok(Math.abs(calculators.calculateFanCurvePressure({
    fanPressure: 250, smokeTemp: 760, fanGasTemp: 619,
    roomTemp: 20, fanElevation: 10, intakeElevation: -1
  }).curvePressure - pressure.curvePressure) < 1e-9);
  assert.throws(() => calculators.calculateSmokeExtraction(sample), /характеристик|curveFlow/i);
  const result = calculators.calculateSmokeExtraction({ ...sample, curveFlow: 14500 });
  assert.equal(result.inputs.curveFlow, 14500);
  assert.equal(result.inputs.fanGasTemp, 619);
  assert.ok(Math.abs(result.intermediate.inletPressure - 44.1884) < 0.001);
  assert.ok(result.outputs.requiredFlow > 0);
  assert.ok(result.outputs.requiredFlow < 14500);
  assert.ok(Math.abs(result.intermediate.curvePressure - 92.9834794) < 0.0001);
  assert.ok(Math.abs(result.intermediate.steps[0].pressureLoss - 4.3758641) < 0.0001);
  assert.ok(Math.abs(result.outputs.requiredFlow - 14424.5450411) < 0.0001);
  assert.equal(result.outputs.withinFifteenPercent, false);
  const report = calculators.renderProtocol(result);
  assert.match(report, /Участок 1: этаж/);
  assert.match(report, /Давление для характеристики вентилятора/);
  assert.match(report, /Суммарная утечка/);
  assert.match(report, /14424[,.]55/);
});

test('duct leakage matches cached spreadsheet numbers without copying its conclusion', () => {
  const result = calculators.calculateDuctLeakage({
    area: 145.925, pressure: 116, pressureDirection: 'positive',
    fanFlow: 4111.2, terminalFlow: 4015.2672, targetClass: 'B'
  });
  assert.ok(Math.abs(result.outputs.leakage - 95.9328) < 0.0001);
  assert.ok(Math.abs(result.outputs.specificLeakage - 0.657411684) < 0.000001);
  assert.equal(result.outputs.actualClass, 'B');
  assert.equal(result.outputs.withinEightPercent, true);
  assert.equal(result.outputs.meetsTargetClass, true);
  assert.ok(Math.abs(result.outputs.limits.B - 0.703149498) < 0.000001);
});

test('duct leakage report sample fails class B even though source PDF claims it passes', () => {
  const result = calculators.calculateDuctLeakage({
    area: 145.925, pressure: 116, pressureDirection: 'positive',
    fanFlow: 4111.2, terminalFlow: 3893.688, targetClass: 'B'
  });
  assert.ok(Math.abs(result.outputs.leakage - 217.512) < 0.0001);
  assert.equal(result.outputs.actualClass, 'A');
  assert.equal(result.outputs.withinEightPercent, true);
  assert.equal(result.outputs.meetsTargetClass, false);
});

test('duct leakage rejects missing, zero and reversed measurements', () => {
  const base = { area: 10, pressure: 100, pressureDirection: 'positive',
    fanFlow: 1000, terminalFlow: 900, targetClass: 'B' };
  assert.throws(() => calculators.calculateDuctLeakage({ ...base, area: '' }), /площад|area/i);
  assert.throws(() => calculators.calculateDuctLeakage({ ...base, pressure: 0 }), /давлен|pressure/i);
  assert.throws(() => calculators.calculateDuctLeakage({ ...base, terminalFlow: 1100 }), /расход|flow/i);
  assert.throws(() => calculators.calculateDuctLeakage({ ...base, fanFlow: '  ' }), /расход|flow/i);
  assert.throws(() => calculators.calculateDuctLeakage({ ...base, pressureDirection: '' }), /направлен/i);
});

test('duct classes use GOST 34060 coefficients and 8 percent is an independent criterion', () => {
  const result = calculators.calculateDuctLeakage({ area: 1000, pressure: 100,
    pressureDirection: 'negative', fanFlow: 1000, terminalFlow: 900, targetClass: 'B' });
  assert.ok(Math.abs(result.outputs.limits.C - 0.011 * 100 ** 0.65) < 1e-12);
  assert.ok(Math.abs(result.outputs.limits.D - 0.004 * 100 ** 0.65) < 1e-12);
  assert.equal(result.outputs.meetsTargetClass, true);
  assert.equal(result.outputs.withinEightPercent, false);
  const report = calculators.renderProtocol(result);
  assert.match(report, /класса B соответствует/);
  assert.match(report, /8 % не соответствует/);
});

test('protocol includes entered values, independent criteria and escapes user metadata', () => {
  const result = calculators.calculateDuctLeakage({ area: 145.925, pressure: 116,
    pressureDirection: 'positive', fanFlow: 4111.2, terminalFlow: 3893.688,
    targetClass: 'B', projectFlow: 4000, projectResistance: 180,
    totalFanPressure: 685, frequency: 50,
    measurements: [{ point: '1', room: 'Комната 1 <script>', Lpr: 4000, Lfact: 3893.688 }] });
  const html = calculators.renderProtocol(result, { systemName: '<script>alert(1)</script>',
    number: 'Расчёт 17', section: 'Первый этаж' });
  assert.match(html, /145[,.]925/);
  assert.match(html, /3893[,.]688/);
  assert.match(html, /217[,.]512/);
  assert.match(html, /класса B[^<]*не соответствует/i);
  assert.match(html, /8 %[^<]*соответствует/i);
  assert.doesNotMatch(html, /<script>/);
  assert.match(html, /&lt;script&gt;/);
  assert.doesNotMatch(html, /Торговый центр «Академический»|Система ДУ1|П11/);
  assert.match(html, /Комната 1 &lt;script&gt;/);
  assert.match(html, /4000/);
  assert.match(html, /685/);
  assert.match(html, /Расчёт 17/);
  assert.match(html, /Первый этаж/);
});

test('duct leakage rejects a terminal sum that disagrees with entered measurement rows', () => {
  assert.throws(() => calculators.calculateDuctLeakage({
    area: 10, pressure: 100, pressureDirection: 'positive',
    fanFlow: 1000, terminalFlow: 900, targetClass: 'B',
    measurements: [{ point: '1', room: 'Зал', Lpr: 900, Lfact: 800 }]
  }), /сумм|решетк/i);
});

test('website displays the current duct result and blocks stale protocols after invalid input', () => {
  const listeners = {};
  const alerts = [];
  const elements = new Map();
  function element(id, value = '') {
    const classes = new Set();
    const item = { id, value, dataset: {}, style: {}, disabled: false,
      innerHTML: '', innerText: '', textContent: '',
      classList: { add: name => classes.add(name), remove: name => classes.delete(name),
        toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
        contains: name => classes.has(name) },
      addEventListener: (name, callback) => { item[name] = callback; } };
    elements.set(id, item);
    return item;
  }
  const nav = element('nav');
  nav.dataset.block = 'block3';
  const avokNav = element('avok-nav');
  avokNav.dataset.block = 'block2';
  const smokeNav = element('smoke-nav');
  smokeNav.dataset.block = 'block1';
  const modal = element('calcProtocolModal');
  const report = element('protocolPrintArea');
  const button = element('b3_btn_protocol');
  element('b3_system', 'Реальная система');
  element('b3_area', '145.925');
  element('b3_pressure', '116');
  element('b3_pressure_direction', 'positive');
  element('b3_Lvent', '4111.2');
  const terminal = element('b3_Lgrille', '4015.2672');
  element('b3_target_class', 'B');
  element('b3_res_Leak');
  element('b3_res_Class');
  element('b3_res_StatusText');
  element('avok_validation_status');
  element('b1_Psv', '250');
  element('b1_Tpg', '760');
  element('b1_Tv', '619');
  element('b1_Tpom', '20');
  element('b1_h_top', '10');
  element('b1_h_bot', '-1');
  element('b1_out_Pdiagr');
  element('b1_res_Psa');
  element('b1_btn_protocol');
  const document = { readyState: 'complete',
    getElementById: id => elements.get(id) || null,
    querySelectorAll: selector => selector === '.calc-nav-card' ? [nav, avokNav, smokeNav] : [],
    addEventListener: (name, callback) => { listeners[name] = callback; } };
  const window = { BISCalculatorCore: calculators };
  const code = fs.readFileSync(path.join(__dirname, '../wp-theme/assets/js/site-calculators.js'), 'utf8');
  vm.runInNewContext(code, { document, window, alert: message => alerts.push(message), console });

  nav.click({ preventDefault() {} });
  assert.match(elements.get('b3_res_Leak').innerHTML, /95[,.]93/);
  assert.equal(elements.get('b3_res_Class').textContent, 'Класс B');
  window.calcEngineOpenProtocol();
  assert.match(report.innerHTML, /95[,.]933/);
  assert.match(report.innerHTML, /Реальная система/);

  terminal.value = '3893.688';
  listeners.input({ target: { matches: () => true } });
  window.calcEngineOpenProtocol();
  assert.equal(elements.get('b3_res_Class').textContent, 'Класс A');
  assert.match(report.innerHTML, /217[,.]512/);
  assert.match(report.innerHTML, /класса B не соответствует/);

  window.calcEngineCloseProtocol();
  elements.get('b3_area').value = '';
  listeners.input({ target: { matches: () => true } });
  assert.equal(button.disabled, true);
  window.calcEngineOpenProtocol();
  assert.equal(modal.classList.contains('active'), false);
  assert.ok(alerts.length > 0);

  avokNav.click({ preventDefault() {} });
  window.calcEngineOpenProtocol();
  assert.match(elements.get('avok_validation_status').textContent, /пока недоступен/);
  assert.equal(modal.classList.contains('active'), false);

  smokeNav.click({ preventDefault() {} });
  assert.equal(elements.get('b1_out_Pdiagr').value, '92.98');
  assert.equal(elements.get('b1_btn_protocol').disabled, true);
});
