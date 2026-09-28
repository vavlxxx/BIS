(function () {
  'use strict';

  const state = {
    currentBlock: 'block1',
    currentAvok: 'du4_1',
    block1Floors: [],
    block3Grilles: [],
    protocolMeta: {
      number: '',
      date: '',
      objectName: '',
      address: '',
      section: '',
      instruments: '',
      engineer: '',
      approver: ''
    },
    systemNames: { block1: null, block3: null },
    lastResults: {}
  };

  const KMS_COMPONENTS = {
    pass: { name: 'Тройник на проход (0.4)', val: 0.4 },
    tee_branch: { name: 'Тройник на ответвление (1.9)', val: 1.9 },
    elbow_90: { name: 'Колено 90° (0.6)', val: 0.6 },
    bend_45: { name: 'Полуотвод 45° (0.4)', val: 0.4 },
    expansion: { name: 'Внезапное расширение (1.0)', val: 1.0 },
    contraction: { name: 'Внезапное сужение (1.0)', val: 1.0 },
    plenum_box: { name: 'Анемостатическая камера решётки (5.6)', val: 5.6 }
  };

  function getKmsShortTitle(f) {
    const items = f.kmsItems || [];
    if (items.length === 0) return Number.isFinite(Number(f.kms)) && f.kms !== '' ? Number(f.kms).toFixed(1) : 'Выбрать КМС';
    if (items.length === 1 && KMS_COMPONENTS[items[0]]) {
      return KMS_COMPONENTS[items[0]].name.split(' (')[0];
    }
    return `${items.length} элм. (${(parseFloat(f.kms) || 0.4).toFixed(1)})`;
  }

  function escapeAttribute(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;')
      .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;');
  }

  let dom = {};

  function init() {
    cacheDom();
    state.protocolMeta.date = document.getElementById('calc_protocol_date')?.value || '';
    bindEvents();
    renderAll();
  }

  function cacheDom() {
    dom.blockNavBtns = document.querySelectorAll('.calc-nav-card');
    dom.blockPanels = document.querySelectorAll('.calc-block-content');
    dom.avokTabBtns = document.querySelectorAll('.avok-tab-item');
    dom.avokPanels = document.querySelectorAll('.avok-calc-content');

    dom.b1TableBody = document.getElementById('b1FloorsTableBody');
    dom.b3TableBody = document.getElementById('b3GrillesTableBody');
    dom.b3BtnAddGrille = document.getElementById('b3BtnAddGrille');
    dom.b3AutoSumGrilles = document.getElementById('b3_auto_sum_grilles');
    dom.b3GrillesSumVal = document.getElementById('b3GrillesSumVal');

    dom.protocolModal = document.getElementById('calcProtocolModal');
    dom.protocolPrintArea = document.getElementById('protocolPrintArea');
  }

  function bindEvents() {
    dom.blockNavBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const block = btn.dataset.block;
        if (!block) return;
        e.preventDefault();
        switchBlock(block);
      });
    });

    dom.avokTabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const avok = btn.dataset.avok;
        switchAvokTab(avok);
      });
    });

    document.addEventListener('input', e => {
      if (e.target.matches('.calc-auto-recalc')) {
        if (e.target.id === 'b3_system') state.systemNames.block3 = null;
        recalculateCurrent();
      }
    });

    const btnAddFloor = document.getElementById('b1BtnAddFloor');
    if (btnAddFloor) btnAddFloor.addEventListener('click', addBlock1Floor);

    const btnApplyKmsAll = document.getElementById('b1BtnApplyKmsToAll');
    if (btnApplyKmsAll) {
      btnApplyKmsAll.addEventListener('click', () => {
        if (!state.block1Floors || state.block1Floors.length === 0) return;
        const sourceKms = state.block1Floors[0].kms;
        const sourceItems = [...(state.block1Floors[0].kmsItems || [])];
        state.block1Floors.forEach(f => {
          f.kms = sourceKms;
          f.kmsItems = [...sourceItems];
        });
        renderBlock1Table();
        recalculateBlock1();
      });
    }

    if (dom.b3BtnAddGrille) {
      dom.b3BtnAddGrille.addEventListener('click', addBlock3Grille);
    }

    if (dom.b3AutoSumGrilles) {
      dom.b3AutoSumGrilles.addEventListener('change', () => {
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    }
  }

  function switchBlock(block) {
    state.currentBlock = block;
    dom.blockNavBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.block === block);
    });
    dom.blockPanels.forEach(panel => {
      panel.style.display = panel.id === `panel-${block}` ? 'block' : 'none';
    });
    recalculateCurrent();
  }

  function switchAvokTab(tab) {
    state.currentAvok = tab;
    dom.avokTabBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.avok === tab);
    });
    dom.avokPanels.forEach(panel => {
      panel.style.display = panel.id === `avok-${tab}` ? 'block' : 'none';
    });

    recalculateCurrent();
  }

  function renderAll() {
    renderBlock1Table();
    renderBlock3GrillesTable();
    recalculateCurrent();
  }

  function rawValue(id) {
    return document.getElementById(id)?.value?.trim() ?? '';
  }

  function collectSmokeInput() {
    return {
      projectFlow: rawValue('b1_Lpr'),
      fanPressure: rawValue('b1_Psv'),
      smokeTemp: rawValue('b1_Tpg'),
      fanGasTemp: rawValue('b1_Tv'),
      roomTemp: rawValue('b1_Tpom'),
      fanElevation: rawValue('b1_h_top'),
      intakeElevation: rawValue('b1_h_bot'),
      curveFlow: rawValue('b1_La'),
      actualFlow: rawValue('b1_Lfact'),
      sections: state.block1Floors.map(f => ({
        floor: f.floor,
        length: f.li, width: f.a, height: f.b,
        localResistance: f.kms,
        friction: f.lambda,
        valveWidth: f.val_a, valveHeight: f.val_b,
        valveAirResistance: f.valve_resistance
      }))
    };
  }

  function recalculateBlock1() {
    const btn = document.getElementById('b1_btn_protocol');
    const hint = document.getElementById('b1_calc_hint');
    const diagram = document.getElementById('b1_out_Pdiagr');
    try {
      const pressureData = window.BISCalculatorCore.calculateFanCurvePressure(collectSmokeInput());
      if (diagram) diagram.value = pressureData.curvePressure.toFixed(2);
      updateSummaryMetric('b1_res_Psa', pressureData.inletPressure.toFixed(2), 'Па');
    } catch (error) {
      if (diagram) diagram.value = '—';
      updateSummaryMetric('b1_res_Psa', '—', '');
    }
    try {
      const result = window.BISCalculatorCore.calculateSmokeExtraction(collectSmokeInput());
      state.lastResults.block1 = result;
      updateSummaryMetric('b1_res_L0', Math.round(result.outputs.requiredFlow).toLocaleString('ru-RU'), 'м³/ч');
      updateSummaryMetric('b1_res_Psa', result.intermediate.inletPressure.toFixed(2), 'Па');
      updateSummaryMetric('b1_res_G0', result.outputs.finalMassFlow.toFixed(3), 'кг/с');
      updateSummaryMetric('b1_res_Leak', (result.outputs.totalLeakageMass * 3600 / result.intermediate.roomDensity).toFixed(2), 'м³/ч');
      const deviationRow = document.getElementById('b1_row_Dev');
      if (deviationRow) {
        deviationRow.style.display = result.outputs.deviationPercent === null ? 'none' : 'flex';
        const deviation = document.getElementById('b1_res_Dev');
        if (deviation && result.outputs.deviationPercent !== null) {
          deviation.textContent = result.outputs.deviationPercent.toFixed(2) + ' %';
          deviation.style.color = result.outputs.withinFifteenPercent ? '#166534' : '#b91c1c';
        }
      }
      const floorResults = result.intermediate.steps.map(step => ({
        P_sn: step.pressure.toFixed(2),
        G_leak: step.valveLeakage.toFixed(4),
        L_curr: Math.round(step.remainingMassFlow * 3600 / result.intermediate.roomDensity)
      }));
      updateBlock1TableOutputs(floorResults);
      if (btn) {
        btn.disabled = false;
        btn.classList.remove('btn-disabled');
        btn.title = 'Сформировать расчетный протокол';
      }
      if (hint) hint.style.display = 'none';
    } catch (error) {
      delete state.lastResults.block1;
      ['b1_res_L0', 'b1_res_G0', 'b1_res_Leak'].forEach(id => updateSummaryMetric(id, '—', ''));
      const deviationRow = document.getElementById('b1_row_Dev');
      if (deviationRow) deviationRow.style.display = 'none';
      if (btn) {
        btn.disabled = true;
        btn.classList.add('btn-disabled');
        btn.title = error.message;
      }
      if (hint) {
        hint.style.display = 'block';
        hint.textContent = error.message;
      }
      updateBlock1TableOutputs(state.block1Floors.map(() => ({ P_sn: '—', G_leak: '—', L_curr: '—' })));
    }
  }

  function renderBlock1Table() {
    if (!dom.b1TableBody) return;
    dom.b1TableBody.innerHTML = '';

    if (state.block1Floors.length === 0) {
      const trEmpty = document.createElement('tr');
      trEmpty.innerHTML = `<td colspan="11" style="padding: 20px; color: var(--text-light); text-align: center;">Этажи пока не добавлены. Нажмите «+ Добавить этаж», чтобы внести параметры шахты.</td>`;
      dom.b1TableBody.appendChild(trEmpty);
      return;
    }

    state.block1Floors.forEach((f, idx) => {
      const tr = document.createElement('tr');
      const curKms = f.kms === '' ? '' : Number(f.kms).toFixed(1);
      const items = f.kmsItems || [];

      tr.innerHTML = `
        <td style="font-weight:600;">
          <input type="number" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="floor" value="${f.floor}" style="width: 55px;">
        </td>
        <td>
          <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="li" value="${f.li}" style="width: 70px;">
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="a" value="${f.a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="b" value="${f.b}" style="width: 55px;">
          </div>
        </td>
        <td>
          <div class="b1-kms-cell-wrap">
            <input type="number" step="0.1" class="calc-table-input b1-floor-kms-val" data-idx="${idx}" value="${curKms}" title="Суммарный КМС этажа (Σξ)" style="width: 50px; font-weight: 700; text-align: center; height: 32px; padding: 2px 4px;">
            <details class="b1-kms-details">
              <summary class="b1-kms-summary" title="Кликните для выбора комбинации сопротивлений">
                <span class="b1-kms-summary-text">${getKmsShortTitle(f)}</span>
                <span style="font-size: 9px; margin-left: 2px;">▼</span>
              </summary>
              <div class="b1-kms-popover">
                ${Object.entries(KMS_COMPONENTS).map(([kId, kObj]) => `
                  <label class="b1-kms-label">
                    <input type="checkbox" class="b1-kms-cb" data-idx="${idx}" data-val="${kObj.val}" data-id="${kId}" ${items.includes(kId) ? 'checked' : ''}>
                    ${kObj.name}
                  </label>
                `).join('')}
              </div>
            </details>
          </div>
        </td>
        <td>
          <input type="number" step="0.001" min="0" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="lambda" value="${f.lambda}" style="width: 65px;">
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_a" value="${f.val_a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_b" value="${f.val_b}" style="width: 55px;">
          </div>
        </td>
        <td>
          <input type="number" step="1" min="0" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="valve_resistance" value="${f.valve_resistance}" style="width: 80px;">
        </td>
        <td id="b1_out_P_${idx}" style="font-weight:600; color:var(--dark);">-</td>
        <td id="b1_out_G_${idx}" style="color:var(--text-light);">-</td>
        <td id="b1_out_L_${idx}" style="font-weight:700; color:var(--primary-dark);">-</td>
        <td>
          <button type="button" class="btn-delete-floor" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить этаж">&times;</button>
        </td>
      `;
      dom.b1TableBody.appendChild(tr);
    });

    dom.b1TableBody.querySelectorAll('.b1-floor-input').forEach(input => {
      input.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const field = e.target.dataset.field;
        state.block1Floors[idx][field] = e.target.value.trim() === '' ? '' : Number(e.target.value);
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.b1-floor-kms-val').forEach(input => {
      input.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const val = e.target.value.trim() === '' ? '' : Number(e.target.value);
        state.block1Floors[idx].kms = val;
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.b1-kms-cb').forEach(cb => {
      cb.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const floor = state.block1Floors[idx];
        if (!floor.kmsItems) floor.kmsItems = [];
        const id = e.target.dataset.id;
        if (e.target.checked) {
          if (!floor.kmsItems.includes(id)) floor.kmsItems.push(id);
        } else {
          floor.kmsItems = floor.kmsItems.filter(x => x !== id);
        }
        let totalKms = 0;
        floor.kmsItems.forEach(kId => {
          if (KMS_COMPONENTS[kId]) totalKms += KMS_COMPONENTS[kId].val;
        });
        floor.kms = floor.kmsItems.length ? Math.round(totalKms * 10) / 10 : '';
        renderBlock1Table();
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.btn-delete-floor').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block1Floors.splice(idx, 1);
        renderBlock1Table();
        recalculateBlock1();
      });
    });
  }

  function updateBlock1TableOutputs(results) {
    results.forEach((r, idx) => {
      const pEl = document.getElementById(`b1_out_P_${idx}`);
      const gEl = document.getElementById(`b1_out_G_${idx}`);
      const lEl = document.getElementById(`b1_out_L_${idx}`);
      if (pEl) pEl.innerText = r.P_sn + ' Па';
      if (gEl) gEl.innerText = r.G_leak + ' кг/с';
      if (lEl) lEl.innerText = r.L_curr.toLocaleString('ru-RU') + ' м³/ч';
    });
  }

  function addBlock1Floor() {
    const lastFloor = state.block1Floors[state.block1Floors.length - 1];
    const newFloorNum = lastFloor ? Math.max(1, lastFloor.floor - 1) : 1;
    state.block1Floors.push({
      floor: newFloorNum,
      li: '',
      a: '',
      b: '',
      kms: '',
      kmsItems: [],
      lambda: '',
      val_a: '',
      val_b: '',
      valve_resistance: ''
    });
    renderBlock1Table();
    recalculateBlock1();
  }

  function recalculateBlock2() {
    delete state.lastResults.avok;
    updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
    updateSummaryMetric('avok_res_sub2_val', '—', '');
    const status = document.getElementById('avok_validation_status');
    if (status) status.textContent = 'Численный расчет пока недоступен: формулы АВОК для этого вида системы не подтверждены. Протокол с непроверенными значениями не формируется.';
  }

  function addBlock3Grille() {
    const nextNum = state.block3Grilles.length + 1;
    state.block3Grilles.push({
      point: `${nextNum}`,
      room: '',
      Lpr: '',
      Lfact: ''
    });
    renderBlock3GrillesTable();
    updateBlock3GrillesSum();
    recalculateBlock3();
  }

  function updateBlock3GrillesSum() {
    const complete = state.block3Grilles.length > 0 && state.block3Grilles.every(g =>
      g.Lfact !== '' && Number.isFinite(Number(g.Lfact)) && Number(g.Lfact) >= 0);
    const sum = complete ? state.block3Grilles.reduce((total, g) => total + Number(g.Lfact), 0) : null;
    if (dom.b3GrillesSumVal) {
      dom.b3GrillesSumVal.innerText = sum === null ? '—' : sum.toFixed(2);
    }
    const autoSumCheckbox = document.getElementById('b3_auto_sum_grilles');
    if (autoSumCheckbox && autoSumCheckbox.checked) {
      const lGrilleInput = document.getElementById('b3_Lgrille');
      if (lGrilleInput) {
        lGrilleInput.value = sum === null ? '' : String(sum);
      }
    }
  }

  function renderBlock3GrillesTable() {
    if (!dom.b3TableBody) return;
    dom.b3TableBody.innerHTML = '';

    state.block3Grilles.forEach((g, idx) => {
      const tr = document.createElement('tr');
      const lpr = parseFloat(g.Lpr) || 0;
      const lfact = parseFloat(g.Lfact) || 0;
      const dev = lpr > 0 ? (((lfact - lpr) / lpr) * 100).toFixed(2) : '—';
      const devColor = (dev !== '—' && Math.abs(parseFloat(dev)) <= 10) ? '#166534' : '#b91c1c';

      tr.innerHTML = `
        <td>
          <input type="text" class="calc-field-input calc-field-input--small b3-grille-point" data-idx="${idx}" value="${escapeAttribute(g.point || (idx + 1))}" style="text-align:center;">
        </td>
        <td>
          <input type="text" class="calc-field-input calc-field-input--small b3-grille-room" data-idx="${idx}" value="${escapeAttribute(g.room || '')}">
        </td>
        <td>
          <input type="number" class="calc-field-input calc-field-input--small b3-grille-lpr" data-idx="${idx}" value="${g.Lpr || ''}" step="10">
        </td>
        <td>
          <input type="number" class="calc-field-input calc-field-input--small b3-grille-lfact" data-idx="${idx}" value="${g.Lfact || ''}" step="0.1">
        </td>
        <td style="font-weight:700; color:${devColor};">
          ${dev !== '—' ? `${dev} %` : '—'}
        </td>
        <td>
          <button type="button" class="btn-delete-grille" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить точку замера">&times;</button>
        </td>
      `;
      dom.b3TableBody.appendChild(tr);
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-point').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].point = e.target.value;
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-room').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].room = e.target.value;
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-lpr').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].Lpr = e.target.value.trim() === '' ? '' : Number(e.target.value);
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-lfact').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].Lfact = e.target.value.trim() === '' ? '' : Number(e.target.value);
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.btn-delete-grille').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block3Grilles.splice(idx, 1);
        renderBlock3GrillesTable();
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    });

    updateBlock3GrillesSum();
  }

  function collectDuctInput() {
    return {
      area: rawValue('b3_area'),
      pressure: rawValue('b3_pressure'),
      pressureDirection: rawValue('b3_pressure_direction'),
      fanFlow: rawValue('b3_Lvent'),
      terminalFlow: rawValue('b3_Lgrille'),
      targetClass: rawValue('b3_target_class'),
      systemName: rawValue('b3_system'),
      projectFlow: rawValue('b3_Lproject'),
      projectResistance: rawValue('b3_net_resistance'),
      totalFanPressure: rawValue('b3_P_fan_total'),
      frequency: rawValue('b3_frequency'),
      measurements: state.block3Grilles.map(g => ({ ...g }))
    };
  }

  function recalculateBlock3() {
    const btn = document.getElementById('b3_btn_protocol');
    const status = document.getElementById('b3_res_StatusText');
    const classEl = document.getElementById('b3_res_Class');
    try {
      const result = window.BISCalculatorCore.calculateDuctLeakage(collectDuctInput());
      state.lastResults.block3 = result;
      const o = result.outputs;
      updateSummaryMetric('b3_res_Leak', o.leakage.toFixed(2), 'м³/ч');
      updateSummaryMetric('b3_res_f_fact', o.specificLeakage.toFixed(4), 'м³/(ч·м²)');
      updateSummaryMetric('b3_res_Deviation', o.deviationPercent.toFixed(2), '%');
      for (const name of ['A', 'B', 'C', 'D']) {
        updateSummaryMetric('b3_res_f' + name, o.limits[name].toFixed(4), 'м³/(ч·м²)');
      }
      if (classEl) classEl.textContent = o.actualClass ? 'Класс ' + o.actualClass : 'Класс не установлен';
      if (status) {
        status.textContent = (o.meetsTargetClass ? 'Предел проектного класса соблюден' : 'Предел проектного класса превышен') +
          '; ' + (o.withinEightPercent ? 'утечка не выше 8 %' : 'утечка выше 8 %');
        status.style.color = o.meetsTargetClass && o.withinEightPercent ? '#047857' : '#b91c1c';
      }
      if (btn) {
        btn.disabled = false;
        btn.classList.remove('btn-disabled');
        btn.title = 'Сформировать расчетный протокол';
      }
    } catch (error) {
      delete state.lastResults.block3;
      ['b3_res_Leak', 'b3_res_f_fact', 'b3_res_Deviation',
        'b3_res_fA', 'b3_res_fB', 'b3_res_fC', 'b3_res_fD']
        .forEach(id => updateSummaryMetric(id, '—', ''));
      if (classEl) classEl.textContent = '—';
      if (status) {
        status.textContent = error.message;
        status.style.color = 'var(--text-light)';
      }
      if (btn) {
        btn.disabled = true;
        btn.classList.add('btn-disabled');
        btn.title = error.message;
      }
    }
  }

  function generateProtocolHTML() {
    if (state.currentBlock === 'block2') {
      throw new Error('Протокол АВОК недоступен до проверки расчетной методики');
    }
    const result = state.currentBlock === 'block1'
      ? state.lastResults.block1 : state.lastResults.block3;
    if (!result) throw new Error('Заполните исходные данные для расчета');
    const meta = { ...state.protocolMeta };
    const name = state.systemNames[state.currentBlock];
    meta.systemName = name === null
      ? (state.currentBlock === 'block3' ? rawValue('b3_system') : '') : name;
    return window.BISCalculatorCore.renderProtocol(result, meta);
  }

  window.calcEngineOpenProtocol = function () {
    recalculateCurrent();
    try {
      const html = generateProtocolHTML();
      const systemNameInput = document.getElementById('calc_protocol_system_name');
      if (systemNameInput) {
        const name = state.systemNames[state.currentBlock];
        systemNameInput.value = name === null
          ? (state.currentBlock === 'block3' ? rawValue('b3_system') : '') : name;
      }
      if (dom.protocolPrintArea) dom.protocolPrintArea.innerHTML = html;
      if (dom.protocolModal) dom.protocolModal.classList.add('active');
    } catch (error) {
      alert(error.message);
    }
  };

  window.calcEngineCloseProtocol = function () {
    if (dom.protocolModal) dom.protocolModal.classList.remove('active');
  };

  window.calcEngineUpdateMeta = function (field, value) {
    if (field === 'systemName') state.systemNames[state.currentBlock] = value;
    else state.protocolMeta[field] = value;
    try {
      if (dom.protocolPrintArea) dom.protocolPrintArea.innerHTML = generateProtocolHTML();
    } catch (error) {
      if (dom.protocolModal) dom.protocolModal.classList.remove('active');
    }
  };

  window.calcEngineDirectPrint = function () {
    recalculateCurrent();
    let report;
    try {
      report = generateProtocolHTML();
    } catch (error) {
      alert(error.message);
      return;
    }
    const printWindow = window.open('', '_blank', 'width=900,height=750');
    if (!printWindow) {
      alert('Разрешите всплывающее окно для печати протокола');
      return;
    }
    printWindow.document.open();
    printWindow.document.write(`<!doctype html><html lang="ru"><head><meta charset="utf-8">
      <title>Протокол расчета</title>
      <style>
        @page { size: A4; margin: 18mm; }
        body { font: 12pt/1.4 Arial, sans-serif; color: #111; }
        h1 { font-size: 19pt; } h2 { font-size: 13pt; margin-top: 20pt; }
        table { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        th, td { padding: 5pt; border: 1px solid #888; text-align: left; vertical-align: top; }
        th { width: 58%; font-weight: 500; }
        .calc-report-conclusion { border: 1px solid #888; padding: 8pt; }
      </style></head><body>${report}<script>
      window.onload = function () { window.focus(); window.print(); };
      <\/script></body></html>`);
    printWindow.document.close();
  };

  function recalculateCurrent() {
    if (state.currentBlock === 'block1') {
      recalculateBlock1();
    } else if (state.currentBlock === 'block2') {
      recalculateBlock2();
    } else if (state.currentBlock === 'block3') {
      recalculateBlock3();
    }
  }

  function updateSummaryMetric(id, val, unit) {
    const el = document.getElementById(id);
    if (el) {
      el.innerHTML = `${val} ${unit ? `<span class="unit">${unit}</span>` : ''}`;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
