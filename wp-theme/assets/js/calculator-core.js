(function (root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.BISCalculatorCore = api;
})(typeof window !== 'undefined' ? window : null, function () {
  'use strict';

  function number(value, label, allowZero = false) {
    if (value === null || value === undefined || typeof value === 'boolean' ||
        (typeof value === 'string' && value.trim() === '')) {
      throw new Error(`Укажите ${label}`);
    }
    const parsed = Number(value);
    if (!Number.isFinite(parsed) || (allowZero ? parsed < 0 : parsed <= 0)) {
      throw new Error(`Проверьте ${label}`);
    }
    return parsed;
  }

  function signedNumber(value, label) {
    if (value === null || value === undefined || typeof value === 'boolean' ||
        (typeof value === 'string' && value.trim() === '')) throw new Error(`Укажите ${label}`);
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) throw new Error(`Проверьте ${label}`);
    return parsed;
  }

  function calculateFanCurvePressure(raw) {
    const input = raw || {};
    const fanPressure = number(input.fanPressure, 'давление вентилятора');
    const smokeTemp = number(input.smokeTemp, 'температуру продуктов горения');
    const fanGasTemp = number(input.fanGasTemp, 'температуру газов у вентилятора');
    const roomTemp = signedNumber(input.roomTemp, 'температуру помещения');
    const fanElevation = signedNumber(input.fanElevation, 'отметку вентилятора');
    const intakeElevation = signedNumber(input.intakeElevation, 'отметку дымоприемного устройства');
    if (roomTemp <= -273.15) throw new Error('Температура помещения ниже абсолютного нуля');
    const roomKelvin = roomTemp + 273.15;
    const roomDensity = 353 / roomKelvin;
    const gasDensity = 353 / fanGasTemp;
    const meanSmokeDensity = 2 * roomDensity * roomKelvin / (smokeTemp + fanGasTemp);
    const heightDifference = fanElevation - intakeElevation;
    const inletPressure = fanPressure * gasDensity / 1.2 +
      9.8 * heightDifference * (meanSmokeDensity - roomDensity);
    if (inletPressure <= 0) throw new Error('Расчетное давление перед вентилятором неположительно');
    return { fanPressure, smokeTemp, fanGasTemp, roomTemp, fanElevation,
      intakeElevation, roomDensity, gasDensity, meanSmokeDensity, heightDifference,
      inletPressure, curvePressure: inletPressure * 1.2 / gasDensity };
  }

  function calculateSmokeExtraction(raw) {
    const input = raw || {};
    const projectFlow = input.projectFlow === '' || input.projectFlow === null || input.projectFlow === undefined
      ? null : number(input.projectFlow, 'проектный расход');
    const { fanPressure, smokeTemp, fanGasTemp, roomTemp, fanElevation, intakeElevation,
      roomDensity, gasDensity, meanSmokeDensity, heightDifference, inletPressure, curvePressure } =
      calculateFanCurvePressure(input);
    const curveFlow = number(input.curveFlow, 'расход по характеристике вентилятора');
    const actualFlow = input.actualFlow === '' || input.actualFlow === undefined || input.actualFlow === null
      ? null : number(input.actualFlow, 'фактический расход', true);
    if (!Array.isArray(input.sections) || input.sections.length === 0) throw new Error('Добавьте участок шахты');

    const sections = input.sections.map((rawSection, index) => {
      const section = rawSection || {};
      const prefix = `участок ${index + 1}: `;
      return {
        floor: section.floor === undefined || section.floor === null || section.floor === ''
          ? null : signedNumber(section.floor, prefix + 'этаж'),
        length: number(section.length, prefix + 'длину'),
        width: number(section.width, prefix + 'ширину шахты'),
        height: number(section.height, prefix + 'высоту шахты'),
        localResistance: number(section.localResistance, prefix + 'местное сопротивление', true),
        friction: number(section.friction, prefix + 'коэффициент трения', true),
        valveWidth: number(section.valveWidth, prefix + 'ширину клапана'),
        valveHeight: number(section.valveHeight, prefix + 'высоту клапана'),
        valveAirResistance: number(section.valveAirResistance, prefix + 'сопротивление клапана')
      };
    });

    let massFlow = roomDensity * curveFlow / 3600;
    const initialMassFlow = massFlow;
    let sectionPressure = inletPressure;
    const steps = sections.map((section, index) => {
      const shaftArea = section.width * section.height;
      const shaftPerimeter = 2 * (section.width + section.height);
      const hydraulicDiameter = 4 * shaftArea / shaftPerimeter;
      const velocity = massFlow / (roomDensity * shaftArea);
      const pressureLoss = 0.5 * roomDensity *
        (section.localResistance + section.friction * section.length / hydraulicDiameter) * velocity ** 2;
      sectionPressure -= pressureLoss;
      if (sectionPressure < 0) throw new Error(`На участке ${index + 1} расчетное давление отрицательно`);
      const valveArea = section.valveWidth * section.valveHeight;
      const valveLeakage = valveArea * Math.sqrt(sectionPressure / section.valveAirResistance);
      massFlow -= valveLeakage;
      if (massFlow < 0) throw new Error(`На участке ${index + 1} утечка превышает расход`);
      return { index: index + 1, shaftArea, hydraulicDiameter, pressureLoss,
        pressure: sectionPressure, valveArea, valveLeakage, remainingMassFlow: massFlow };
    });

    const requiredFlow = massFlow * 3600 / roomDensity;
    const deviationPercent = actualFlow === null ? null : (actualFlow - requiredFlow) / requiredFlow * 100;
    return {
      kind: 'smokeExtraction',
      method: 'ГОСТ Р 53300-2009, приложение Б',
      inputs: { projectFlow, fanPressure, smokeTemp, fanGasTemp, roomTemp,
        fanElevation, intakeElevation, curveFlow, actualFlow, sections },
      intermediate: { roomDensity, gasDensity, meanSmokeDensity, heightDifference,
        inletPressure, curvePressure, initialMassFlow, steps },
      outputs: { requiredFlow, finalMassFlow: massFlow, totalLeakageMass: initialMassFlow - massFlow,
        deviationPercent, withinFifteenPercent: deviationPercent === null ? null : Math.abs(deviationPercent) <= 15 }
    };
  }

  const CLASS_COEFFICIENTS = Object.freeze({ A: 0.097, B: 0.032, C: 0.011, D: 0.004 });

  function calculateDuctLeakage(raw) {
    const input = raw || {};
    const area = number(input.area, 'развернутую площадь');
    const pressure = number(input.pressure, 'статическое давление');
    const fanFlow = number(input.fanFlow, 'расход у вентилятора');
    const terminalFlow = number(input.terminalFlow, 'расход по решеткам', true);
    const pressureDirection = input.pressureDirection;
    const targetClass = input.targetClass;
    if (!['positive', 'negative'].includes(pressureDirection)) throw new Error('Укажите направление давления');
    if (!Object.hasOwn(CLASS_COEFFICIENTS, targetClass)) throw new Error('Укажите проектный класс герметичности');
    if (terminalFlow > fanFlow) throw new Error('Расход по решеткам больше расхода у вентилятора');

    const measurements = Array.isArray(input.measurements) ? input.measurements.map((rawPoint, index) => ({
      point: String(rawPoint.point || index + 1),
      room: String(rawPoint.room || ''),
      Lpr: rawPoint.Lpr === '' || rawPoint.Lpr === undefined ? null : number(rawPoint.Lpr, `проектный расход точки ${index + 1}`, true),
      Lfact: number(rawPoint.Lfact, `фактический расход точки ${index + 1}`, true)
    })) : [];
    if (measurements.length > 0) {
      const measuredSum = measurements.reduce((sum, point) => sum + point.Lfact, 0);
      if (Math.abs(measuredSum - terminalFlow) > 0.01) {
        throw new Error('Сумма замеров по решеткам не совпадает с общим расходом');
      }
    }
    const optional = {};
    for (const [key, label] of [
      ['projectFlow', 'проектный расход'],
      ['projectResistance', 'проектное сопротивление'],
      ['totalFanPressure', 'полное давление вентилятора'],
      ['frequency', 'частоту электродвигателя']
    ]) {
      optional[key] = input[key] === '' || input[key] === null || input[key] === undefined
        ? null : number(input[key], label, true);
    }

    const leakage = fanFlow - terminalFlow;
    const specificLeakage = leakage / area;
    const deviationPercent = leakage / fanFlow * 100;
    const limits = Object.fromEntries(Object.entries(CLASS_COEFFICIENTS)
      .map(([name, coefficient]) => [name, coefficient * pressure ** 0.65]));
    const eligible = ['D', 'C', 'B', 'A'].filter(name => specificLeakage <= limits[name]);
    const actualClass = eligible.length ? eligible[0] : null;
    const withinEightPercent = deviationPercent <= 8;
    const meetsTargetClass = specificLeakage <= limits[targetClass];
    return {
      kind: 'ductLeakage', method: 'ГОСТ 34060-2017, пункты 7.3–7.5',
      inputs: { area, pressure, pressureDirection, fanFlow, terminalFlow, targetClass,
        measurements, ...optional },
      outputs: { leakage, specificLeakage, deviationPercent, limits,
        actualClass, withinEightPercent, meetsTargetClass }
    };
  }

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function inputNumber(value) {
    return escapeHtml(String(value).replace('.', ','));
  }

  function outputNumber(value, digits = 3) {
    return escapeHtml(Number(value).toFixed(digits).replace('.', ','));
  }

  function row(label, value, unit = '') {
    return `<tr><th scope="row">${escapeHtml(label)}</th><td>${value}${unit ? ` ${escapeHtml(unit)}` : ''}</td></tr>`;
  }

  function renderProtocol(result, meta = {}) {
    if (!result || !result.kind) throw new Error('Нет проверенного результата расчета');
    const title = result.kind === 'smokeExtraction'
      ? 'Расчет расхода через дымоприемное устройство'
      : 'Расчет герметичности воздуховодов';
    const methodUrl = result.kind === 'smokeExtraction'
      ? 'https://protect.gost.ru/gost/details/8f5e6de8-3d05-45ec-b59a-b4d6f4236678'
      : 'https://protect.gost.ru/gost/details/8629d8cd-4237-4ac6-a30c-d3ebda9445f2';
    const details = [
      row('Наименование системы', escapeHtml(meta.systemName || 'Не указано')),
      row('Объект', escapeHtml(meta.objectName || 'Не указан')),
      row('Адрес', escapeHtml(meta.address || 'Не указан')),
      row('Исполнитель', escapeHtml(meta.engineer || 'Не указан')),
      row('Методика', `<a href="${methodUrl}" target="_blank" rel="noopener noreferrer">${escapeHtml(result.method)}</a>`)
    ];
    for (const [key, label] of [
      ['number', 'Номер расчета'], ['date', 'Дата'], ['section', 'Участок'],
      ['instruments', 'Средства измерения'], ['approver', 'Руководитель']
    ]) {
      if (meta[key]) details.push(row(label, escapeHtml(meta[key])));
    }
    const inputRows = [];
    const outputRows = [];
    let conclusion = '';
    if (result.kind === 'smokeExtraction') {
      const i = result.inputs;
      inputRows.push(
        row('Давление вентилятора', inputNumber(i.fanPressure), 'Па'),
        row('Температура продуктов горения', inputNumber(i.smokeTemp), 'К'),
        row('Температура газов у вентилятора', inputNumber(i.fanGasTemp), 'К'),
        row('Температура помещения', inputNumber(i.roomTemp), '°C'),
        row('Отметка вентилятора', inputNumber(i.fanElevation), 'м'),
        row('Отметка дымоприемного устройства', inputNumber(i.intakeElevation), 'м'),
        row('Расход с характеристики вентилятора', inputNumber(i.curveFlow), 'м³/ч'),
        row('Фактический измеренный расход', i.actualFlow === null ? 'Не указан' : inputNumber(i.actualFlow), 'м³/ч')
      );
      if (i.projectFlow !== null) inputRows.unshift(row('Проектный расход вентилятора', inputNumber(i.projectFlow), 'м³/ч'));
      i.sections.forEach((section, index) => {
        const prefix = `Участок ${index + 1}: `;
        if (section.floor !== null) inputRows.push(row(prefix + 'этаж', inputNumber(section.floor)));
        inputRows.push(
          row(prefix + 'длина', inputNumber(section.length), 'м'),
          row(prefix + 'сечение шахты', `${inputNumber(section.width)} × ${inputNumber(section.height)}`, 'м'),
          row(prefix + 'местное сопротивление', inputNumber(section.localResistance)),
          row(prefix + 'коэффициент трения', inputNumber(section.friction)),
          row(prefix + 'сечение клапана', `${inputNumber(section.valveWidth)} × ${inputNumber(section.valveHeight)}`, 'м'),
          row(prefix + 'сопротивление воздухопроницанию клапана', inputNumber(section.valveAirResistance))
        );
      });
      outputRows.push(
        row('Плотность воздуха в помещении', outputNumber(result.intermediate.roomDensity, 4), 'кг/м³'),
        row('Плотность газа у вентилятора', outputNumber(result.intermediate.gasDensity, 4), 'кг/м³'),
        row('Средняя плотность продуктов горения', outputNumber(result.intermediate.meanSmokeDensity, 4), 'кг/м³'),
        row('Разность отметок', outputNumber(result.intermediate.heightDifference, 2), 'м'),
        row('Давление перед вентилятором', outputNumber(result.intermediate.inletPressure, 2), 'Па'),
        row('Давление для характеристики вентилятора', outputNumber(result.intermediate.curvePressure, 2), 'Па'),
        row('Массовый расход по характеристике вентилятора', outputNumber(result.intermediate.initialMassFlow, 4), 'кг/с')
      );
      result.intermediate.steps.forEach(step => {
        outputRows.push(row(`Участок ${step.index}: площадь шахты`, outputNumber(step.shaftArea, 3), 'м²'));
        outputRows.push(row(`Участок ${step.index}: гидравлический диаметр`, outputNumber(step.hydraulicDiameter, 3), 'м'));
        outputRows.push(row(`Участок ${step.index}: потери давления`, outputNumber(step.pressureLoss, 2), 'Па'));
        outputRows.push(row(`Участок ${step.index}: давление`, outputNumber(step.pressure, 2), 'Па'));
        outputRows.push(row(`Участок ${step.index}: площадь клапана`, outputNumber(step.valveArea, 3), 'м²'));
        outputRows.push(row(`Участок ${step.index}: утечка`, outputNumber(step.valveLeakage, 4), 'кг/с'));
        outputRows.push(row(`Участок ${step.index}: остаточный массовый расход`, outputNumber(step.remainingMassFlow, 4), 'кг/с'));
      });
      outputRows.push(row('Суммарная утечка', outputNumber(result.outputs.totalLeakageMass, 4), 'кг/с'));
      outputRows.push(row('Итоговый массовый расход', outputNumber(result.outputs.finalMassFlow, 4), 'кг/с'));
      outputRows.push(row('Требуемый расход', outputNumber(result.outputs.requiredFlow, 2), 'м³/ч'));
      if (result.outputs.deviationPercent !== null) {
        outputRows.push(row('Отклонение фактического расхода', outputNumber(result.outputs.deviationPercent, 2), '%'));
      }
      conclusion = result.outputs.withinFifteenPercent === null
        ? 'Фактический расход не введен; оценка по допуску не выполнена.'
        : `Отклонение ${result.outputs.withinFifteenPercent ? 'в пределах' : 'за пределами'} 15 %.`;
    } else if (result.kind === 'ductLeakage') {
      const i = result.inputs;
      inputRows.push(
        row('Развернутая площадь воздуховодов', inputNumber(i.area), 'м²'),
        row('Среднее статическое давление', inputNumber(i.pressure), 'Па'),
        row('Направление давления', i.pressureDirection === 'positive' ? 'Положительное' : 'Отрицательное'),
        row('Расход у вентилятора', inputNumber(i.fanFlow), 'м³/ч'),
        row('Суммарный расход по решеткам', inputNumber(i.terminalFlow), 'м³/ч'),
        row('Проектный класс', escapeHtml(i.targetClass))
      );
      for (const [key, label, unit] of [
        ['projectFlow', 'Проектный расход', 'м³/ч'],
        ['projectResistance', 'Проектное сопротивление', 'Па'],
        ['totalFanPressure', 'Полное давление вентилятора', 'Па'],
        ['frequency', 'Частота электродвигателя', 'Гц']
      ]) {
        if (i[key] !== null) inputRows.push(row(label, inputNumber(i[key]), unit));
      }
      i.measurements.forEach((point, index) => {
        const label = `Точка ${point.point || index + 1}${point.room ? `, ${point.room}` : ''}`;
        if (point.Lpr !== null) inputRows.push(row(`${label}: проектный расход`, inputNumber(point.Lpr), 'м³/ч'));
        inputRows.push(row(`${label}: фактический расход`, inputNumber(point.Lfact), 'м³/ч'));
      });
      outputRows.push(
        row('Утечка воздуха', outputNumber(result.outputs.leakage), 'м³/ч'),
        row('Удельная утечка', outputNumber(result.outputs.specificLeakage, 4), 'м³/(ч·м²)'),
        row('Доля утечки', outputNumber(result.outputs.deviationPercent, 2), '%')
      );
      for (const name of ['A', 'B', 'C', 'D']) {
        outputRows.push(row(`Предел класса ${name}`, outputNumber(result.outputs.limits[name], 4), 'м³/(ч·м²)'));
      }
      outputRows.push(row('Расчетный класс', escapeHtml(result.outputs.actualClass || 'Не установлен')));
      conclusion = `Пределу класса ${escapeHtml(i.targetClass)} ${result.outputs.meetsTargetClass ? 'соответствует' : 'не соответствует'}; ` +
        `ограничению 8 % ${result.outputs.withinEightPercent ? 'соответствует' : 'не соответствует'}.`;
    } else {
      throw new Error('Неизвестный вид расчета');
    }
    return `<article class="calc-report"><h1>${title}</h1>` +
      `<h2>Реквизиты</h2><table>${details.join('')}</table>` +
      `<h2>Исходные данные</h2><table>${inputRows.join('')}</table>` +
      `<h2>Промежуточные и итоговые значения</h2><table>${outputRows.join('')}</table>` +
      `<p class="calc-report-conclusion">${conclusion}</p></article>`;
  }

  return { calculateFanCurvePressure, calculateSmokeExtraction, calculateDuctLeakage, renderProtocol };
});
