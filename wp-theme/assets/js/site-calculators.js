(function () {
  'use strict';

  const state = {
    currentBlock: 'block1',
    currentAvok: 'du4_1',
    activeElementType: 'D1',
    block1Floors: [
      { floor: 1, li: 14.0, a: 0.6, b: 0.45, kmsType: 'standard', val_a: 0.77, val_b: 0.75 },
      { floor: 2, li: 4.0, a: 0.6, b: 0.45, kmsType: 'standard', val_a: 0.77, val_b: 0.75 }
    ],
    block3Elements: [],
    protocolMeta: {
      number: '109.005/П-02',
      date: new Date().toISOString().split('T')[0],
      objectName: 'Торговый центр «Академический»',
      address: 'СПб, Гражданский проспект, квартал 9А',
      systemName: 'Система ДУ1',
      section: 'Цокольный этаж, клапан №1',
      instruments: 'Дифференциальный манометр Testo 510, Термоанемометр Testo 417',
      engineer: 'Иванов И.И.',
      approver: 'Петров П.П.'
    },
    lastResults: {}
  };

  const KMS_RATES = {
    standard: 0.4,
    complex: 4.6,
    turns: 1.6
  };

  const LEAKAGE_CLASSES = {
    A: { name: 'Класс A (Низкая плотность)', c: 0.027 },
    B: { name: 'Класс B (Плотный)', c: 0.009 },
    C: { name: 'Класс C (Высокая плотность)', c: 0.003 }
  };

  let dom = {};

  function init() {
    cacheDom();
    bindEvents();
    renderAll();
  }

  function cacheDom() {
    dom.blockNavBtns = document.querySelectorAll('.calc-nav-card');
    dom.blockPanels = document.querySelectorAll('.calc-block-content');
    dom.avokTabBtns = document.querySelectorAll('.avok-tab-item');
    dom.avokPanels = document.querySelectorAll('.avok-calc-content');

    dom.b1TableBody = document.getElementById('b1FloorsTableBody');
    dom.b3TableBody = document.getElementById('b3ElementsTableBody');

    dom.protocolModal = document.getElementById('calcProtocolModal');
    dom.protocolPrintArea = document.getElementById('protocolPrintArea');
    dom.calcElementModal = document.getElementById('calcElementModal');
  }

  function bindEvents() {
    dom.blockNavBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const block = btn.dataset.block;
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
        recalculateCurrent();
      }
    });

    const btnAddFloor = document.getElementById('b1BtnAddFloor');
    if (btnAddFloor) btnAddFloor.addEventListener('click', addBlock1Floor);

    const elCards = document.querySelectorAll('.element-select-card');
    elCards.forEach(card => {
      card.addEventListener('click', () => {
        const type = card.dataset.type;
        openAddElementDialog(type);
      });
    });
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
    renderBlock3Table();
    recalculateCurrent();
  }

  function recalculateBlock1() {
    const rawLpr = document.getElementById('b1_Lpr')?.value;
    const rawPsv = document.getElementById('b1_Psv')?.value;

    if (!rawLpr || !rawPsv) {
      updateSummaryMetric('b1_res_L0', '—', 'м³/ч');
      updateSummaryMetric('b1_res_Psa', '—', 'Па');
      updateSummaryMetric('b1_res_G0', '—', 'кг/с');
      updateSummaryMetric('b1_res_Leak', '—', 'м³/ч');
      const devRow = document.getElementById('b1_row_Dev');
      if (devRow) devRow.style.display = 'none';
      return;
    }

    const Lpr = parseFloat(rawLpr) || 27500;
    const Psv = parseFloat(rawPsv) || 250;
    const Tpg = parseFloat(document.getElementById('b1_Tpg')?.value) || 760;
    const Tpom = parseFloat(document.getElementById('b1_Tpom')?.value) || 20;
    const h_top = parseFloat(document.getElementById('b1_h_top')?.value) || 10.0;
    const h_bot = parseFloat(document.getElementById('b1_h_bot')?.value) || -1.0;
    const rawLfact = document.getElementById('b1_Lfact')?.value;
    const Lfact = rawLfact ? parseFloat(rawLfact) : null;

    const Tv = Tpg > 100 ? (Tpg - 62) : 619;
    const Ta = 273 + Tpom;
    const rho_a = 353 / Ta;
    const rho_sm = (2 * rho_a * Ta) / (Tpg + Tv);
    const rho_v = 353 / Tv;
    const h = Math.max(0, h_top - h_bot);

    const Psa = (Psv * rho_v / 1.2) + (9.81 * h * (rho_a - rho_sm));
    const La = Math.round(Lpr);
    const Ga = (La * rho_a) / 3600;

    let currentG = Ga;
    let currentP = Psa;
    let totalValveLeakage = 0;
    const floorResults = [];

    state.block1Floors.forEach((f, idx) => {
      const F_shaft = (f.a || 0.6) * (f.b || 0.45);
      const P_shaft = 2 * ((f.a || 0.6) + (f.b || 0.45));
      const de = P_shaft > 0 ? (4 * F_shaft / P_shaft) : 1.0;
      const kms = KMS_RATES[f.kmsType] !== undefined ? KMS_RATES[f.kmsType] : 0.4;
      const lambda = 0.016;
      const li = f.li || 3.0;

      const G_before = currentG;
      const velocity_mass = (F_shaft > 0 && rho_a > 0) ? (currentG / (rho_a * F_shaft)) : 0;
      const deltaP = 0.5 * rho_a * (kms + (lambda * li / de)) * Math.pow(velocity_mass, 2);
      currentP = Math.max(0, currentP - deltaP);

      const F_val = Math.max(0, (f.val_a || 0.8) * (f.val_b || 0.75));
      const S_dpn = 10000; // Удельное сопротивление воздухопроницанию по ГОСТ Р 53300-2009
      const G_leak = F_val > 0 ? Math.sqrt(currentP / S_dpn) * F_val : 0;
      totalValveLeakage += G_leak;

      currentG = Math.max(0, currentG - G_leak);
      const L_floor = (currentG * 3600) / rho_a;

      floorResults.push({
        idx: idx + 1,
        floor: f.floor,
        kms: kms.toFixed(1),
        lambda: lambda.toFixed(3),
        li: li.toFixed(1),
        de: de.toFixed(3),
        Fn: F_shaft.toFixed(3),
        Pn: P_shaft.toFixed(2),
        Ga_curr: G_before.toFixed(3),
        P_sn: currentP.toFixed(2),
        F_val: F_val.toFixed(4),
        G_leak: G_leak.toFixed(4),
        G_curr: currentG.toFixed(3),
        L_curr: Math.round(L_floor)
      });
    });

    const G0 = Math.max(0, Ga - totalValveLeakage);
    const L0 = (G0 * 3600) / rho_a;

    updateSummaryMetric('b1_res_L0', Math.round(L0).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('b1_res_Psa', Math.round(Psa).toLocaleString('ru-RU'), 'Па');
    updateSummaryMetric('b1_res_G0', G0.toFixed(2), 'кг/с');
    updateSummaryMetric('b1_res_Leak', Math.round(totalValveLeakage * 3600 / rho_a).toLocaleString('ru-RU'), 'м³/ч');

    let deviation = null;
    if (Lfact && L0 > 0) {
      deviation = ((Lfact - L0) / L0) * 100;
      const devRow = document.getElementById('b1_row_Dev');
      const devEl = document.getElementById('b1_res_Dev');
      if (devRow && devEl) {
        devRow.style.display = 'flex';
        devEl.innerText = (deviation > 0 ? '+' : '') + deviation.toFixed(1) + ' %';
        if (Math.abs(deviation) <= 15) {
          devRow.style.background = '#f0fdf4';
          devRow.style.borderColor = '#bbf7d0';
          devEl.style.color = '#166534';
        } else {
          devRow.style.background = '#fef2f2';
          devRow.style.borderColor = '#fecaca';
          devEl.style.color = '#b91c1c';
        }
      }
    } else {
      const devRow = document.getElementById('b1_row_Dev');
      if (devRow) devRow.style.display = 'none';
    }

    state.lastResults.block1 = {
      Lpr, Psv, Tpg, Tpom, h_top, h_bot, Tv, Ta, rho_a, rho_sm, rho_v, h,
      Psa: Math.round(Psa * 100) / 100,
      La,
      Ga: Ga.toFixed(3),
      G0: G0.toFixed(3),
      L0: Math.round(L0),
      Lfact,
      deviation,
      totalLeakage: Math.round(totalValveLeakage * 3600 / rho_a),
      totalValveLeakageKg: totalValveLeakage.toFixed(4),
      floorResults
    };

    updateBlock1TableOutputs(floorResults);
  }

  function renderBlock1Table() {
    if (!dom.b1TableBody) return;
    dom.b1TableBody.innerHTML = '';

    if (state.block1Floors.length === 0) {
      const trEmpty = document.createElement('tr');
      trEmpty.innerHTML = `<td colspan="9" style="padding: 20px; color: var(--text-light); text-align: center;">Этажи пока не добавлены. Нажмите «+ Добавить этаж», чтобы внести параметры шахты.</td>`;
      dom.b1TableBody.appendChild(trEmpty);
      return;
    }

    state.block1Floors.forEach((f, idx) => {
      const tr = document.createElement('tr');
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
          <select class="calc-table-select b1-floor-input" data-idx="${idx}" data-field="kmsType" style="width: 140px;">
            <option value="standard" ${f.kmsType === 'standard' ? 'selected' : ''}>Проход (0.4)</option>
            <option value="complex" ${f.kmsType === 'complex' ? 'selected' : ''}>Отводы + Тройник (4.6)</option>
            <option value="turns" ${f.kmsType === 'turns' ? 'selected' : ''}>Повороты (1.6)</option>
          </select>
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_a" value="${f.val_a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_b" value="${f.val_b}" style="width: 55px;">
          </div>
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
      input.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const field = e.target.dataset.field;
        if (field === 'kmsType') {
          state.block1Floors[idx].kmsType = e.target.value;
        } else {
          state.block1Floors[idx][field] = parseFloat(e.target.value) || 0;
        }
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
      li: 3.0,
      a: 1.0,
      b: 0.8,
      kmsType: 'standard',
      val_a: 0.8,
      val_b: 0.5
    });
    renderBlock1Table();
    recalculateBlock1();
  }

  function recalculateBlock2() {
    switch (state.currentAvok) {
      case 'du4_1': calcAvokDU4_1(); break;
      case 'pd4_1': calcAvokPD4_1(); break;
      case 'pd4_2': calcAvokPD4_2(); break;
      case 'pd4_7': calcAvokPD4_7(); break;
      case 'pd4_8': calcAvokPD4_8(); break;
      case 'pd7_a': calcAvokPD7_a(); break;
    }
  }

  function calcAvokDU4_1() {
    const rawLk = document.getElementById('du4_1_Lk')?.value;
    const rawQ = document.getElementById('du4_1_Q')?.value;

    if (!rawLk || !rawQ) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const Lk = parseFloat(rawLk) || 0;
    const Bk = parseFloat(document.getElementById('du4_1_Bk')?.value) || 2.4;
    const Hk = parseFloat(document.getElementById('du4_1_Hk')?.value) || 2.8;
    const Q = parseFloat(rawQ) || 0;
    const Ta = 24;

    const smoke_layer_h = 0.5 * Hk;
    const G_smoke = 0.071 * Math.pow(Q, 1/3) * Math.pow(Math.max(0.1, Hk - smoke_layer_h), 5/3) + 0.0018 * Q;
    const T_smoke = Ta + (G_smoke > 0 ? (Q / (1.005 * G_smoke)) : 0);
    const rho_smoke = 353 / (273 + Math.min(600, T_smoke));
    const L_smoke = (G_smoke / rho_smoke) * 3600;
    const P_fan = 380;

    updateSummaryMetric('avok_res_main_val', Math.round(L_smoke).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', P_fan, 'Па');
    updateSummaryMetric('avok_res_sub2_val', Math.round(T_smoke) + ' °C', '');

    state.lastResults.avok = {
      type: 'ДУ4-1 (Дымоудаление из коридора)',
      mainLabel: 'Расход дымоудаления L',
      mainVal: `${Math.round(L_smoke).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление вентилятора Psv',
      sub1Val: `${P_fan} Па`,
      sub2Label: 'Температура дыма',
      sub2Val: `${Math.round(T_smoke)} °C`,
      details: [
        { label: 'Массовый расход дыма G', val: `${G_smoke.toFixed(2)} кг/с` },
        { label: 'Плотность дыма ρsm', val: `${rho_smoke.toFixed(3)} кг/м³` },
        { label: 'Длина коридора Lк', val: `${Lk} м` }
      ]
    };
  }

  function calcAvokPD4_1() {
    const rawFloors = document.getElementById('pd4_1_floors')?.value;
    if (!rawFloors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const floors = parseFloat(rawFloors) || 0;
    const b_door = parseFloat(document.getElementById('pd4_1_b_door')?.value) || 0.9;
    const h_door = parseFloat(document.getElementById('pd4_1_h_door')?.value) || 2.1;
    const b_type = document.getElementById('pd4_1_building_type')?.value || 'living';
    const v_min = b_type === 'living' ? 1.3 : 1.5;

    const F_door = b_door * h_door;
    const rho = 1.25;
    const G_door = v_min * F_door * rho;
    const G_leaks = Math.max(0, floors - 1) * Math.sqrt(35 / 196000);
    const G_total = G_door + G_leaks;
    const L_total = (G_total * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '45', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G_total.toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-1 (Подпор в лестничную клетку)',
      mainLabel: 'Расход подпора в ЛК',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Избыточное давление',
      sub1Val: '45 Па',
      sub2Label: 'Массовый расход G',
      sub2Val: `${G_total.toFixed(2)} кг/с`,
      details: [
        { label: 'Расход через открытую дверь', val: `${(G_door * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Утечки через закрытые двери', val: `${(G_leaks * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Скорость в проеме двери', val: `${v_min} м/с` }
      ]
    };
  }

  function calcAvokPD4_2() {
    const rawFloors = document.getElementById('pd4_2_floors')?.value;
    if (!rawFloors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const floors = parseFloat(rawFloors) || 0;
    const elevators = parseFloat(document.getElementById('pd4_2_elevators')?.value) || 1;
    const rho = 1.25;

    const L_per_door = 3600 * 0.65 * 0.05 * Math.sqrt(2 * 30 / rho);
    const L_total = elevators * (L_per_door * floors + 1800);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '35', 'Па');
    updateSummaryMetric('avok_res_sub2_val', (L_total * rho / 3600).toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-2 (Подпор в шахту лифта)',
      mainLabel: 'Расход подпора в шахту',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в шахте',
      sub1Val: '35 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${(L_total * rho / 3600).toFixed(2)} кг/с`,
      details: [
        { label: 'Количество шахт лифтов', val: `${elevators}` },
        { label: 'Этажность здания', val: `${floors}` }
      ]
    };
  }

  function calcAvokPD4_7() {
    const rawW = document.getElementById('pd4_7_w')?.value;
    if (!rawW) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const w = parseFloat(rawW) || 0;
    const h = parseFloat(document.getElementById('pd4_7_h')?.value) || 2.1;
    const v = parseFloat(document.getElementById('pd4_7_v')?.value) || 1.3;
    const rho = 1.25;

    const F = w * h;
    const G = v * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '25', 'Па');
    updateSummaryMetric('avok_res_sub2_val', v.toFixed(2) + ' м/с', '');

    state.lastResults.avok = {
      type: 'ПД4-7 (Зона ПБЗ - открытая дверь)',
      mainLabel: 'Расход воздуха в ПБЗ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Требуемый подпор',
      sub1Val: '25 Па',
      sub2Label: 'Скорость в проеме',
      sub2Val: `${v} м/с`,
      details: [
        { label: 'Площадь дверного проема F', val: `${F.toFixed(2)} м²` },
        { label: 'Массовый расход G', val: `${G.toFixed(2)} кг/с` }
      ]
    };
  }

  function calcAvokPD4_8() {
    const rawW = document.getElementById('pd4_8_w')?.value;
    if (!rawW) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const w = parseFloat(rawW) || 0;
    const h = parseFloat(document.getElementById('pd4_8_h')?.value) || 2.1;
    const rho = 1.25;

    const F = w * h;
    const G = 1.3 * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '30', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G.toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-8 (Тамбур-шлюз перед ЛК)',
      mainLabel: 'Расход воздуха в ТШ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в ТШ',
      sub1Val: '30 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${G.toFixed(2)} кг/с`,
      details: [
        { label: 'Площадь проема ТШ', val: `${F.toFixed(2)} м²` }
      ]
    };
  }

  function calcAvokPD7_a() {
    const rawDoors = document.getElementById('pd7_a_doors')?.value;
    if (!rawDoors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const doors = parseFloat(rawDoors) || 0;
    const reqP = parseFloat(document.getElementById('pd7_a_reqP')?.value) || 20;
    const rho = 1.25;

    const G_leak = doors * Math.sqrt(reqP / 196000);
    const L_total = Math.max(350, (G_leak * 3600) / rho);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', reqP, 'Па');
    updateSummaryMetric('avok_res_sub2_val', (G_leak * 1000).toFixed(1) + ' г/с', '');

    state.lastResults.avok = {
      type: 'ПД7-а (Зона ПБЗ - закрытая дверь)',
      mainLabel: 'Расход для подпора в ПБЗ',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Поддерживаемое давление',
      sub1Val: `${reqP} Па`,
      sub2Label: 'Суммарная утечка',
      sub2Val: `${(G_leak * 1000).toFixed(1)} г/с`,
      details: [
        { label: 'Количество противопожарных дверей', val: `${doors}` }
      ]
    };
  }

  function recalculateBlock3() {
    const factP = parseFloat(document.getElementById('b3_factP')?.value) || 400;
    const factL = parseFloat(document.getElementById('b3_factL')?.value) || 25;
    const reqClass = document.getElementById('b3_reqClass')?.value || 'B';

    let totalS = 0;
    state.block3Elements.forEach(el => {
      totalS += el.s;
    });
    totalS = Math.max(0.1, totalS);

    const factLeak = factL / totalS;
    const classConfig = LEAKAGE_CLASSES[reqClass] || LEAKAGE_CLASSES.B;
    const allowLeak = classConfig.c * Math.pow(factP, 0.65) * 3.6;
    const isPassed = factLeak <= allowLeak;

    updateSummaryMetric('b3_res_totalS', totalS.toFixed(2), 'м²');
    updateSummaryMetric('b3_res_factLeak', factLeak.toFixed(2), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_allowLeak', allowLeak.toFixed(2), 'м³/(ч·м²)');

    const verdictEl = document.getElementById('b3_verdict_badge');
    if (verdictEl) {
      if (isPassed) {
        verdictEl.className = 'calc-status-badge calc-status-badge--success';
        verdictEl.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> СООТВЕТСТВУЕТ КЛАССУ ${reqClass}`;
      } else {
        verdictEl.className = 'calc-status-badge calc-status-badge--danger';
        verdictEl.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> НЕ СООТВЕТСТВУЕТ (Превышение ${((factLeak/allowLeak - 1)*100).toFixed(0)}%)`;
      }
    }

    state.lastResults.block3 = {
      factP, factL, reqClass,
      totalS: totalS.toFixed(2),
      factLeak: factLeak.toFixed(2),
      allowLeak: allowLeak.toFixed(2),
      isPassed,
      elements: [...state.block3Elements]
    };
  }

  function renderBlock3Table() {
    if (!dom.b3TableBody) return;
    dom.b3TableBody.innerHTML = '';

    state.block3Elements.forEach((el, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="font-weight:600;">${idx + 1}</td>
        <td style="text-align:left; font-weight:600;">${el.name}</td>
        <td><span class="calc-norm-pill" style="font-size:11px;">${el.type}</span></td>
        <td style="text-align:left; font-size:13px; color:var(--text);">${formatElementParams(el)}</td>
        <td style="font-weight:700; color:var(--primary-dark);">${el.s.toFixed(2)} м²</td>
        <td>
          <button type="button" class="btn-delete-element" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить элемент">&times;</button>
        </td>
      `;
      dom.b3TableBody.appendChild(tr);
    });

    dom.b3TableBody.querySelectorAll('.btn-delete-element').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block3Elements.splice(idx, 1);
        renderBlock3Table();
        recalculateBlock3();
      });
    });
  }

  function formatElementParams(el) {
    const p = el.params;
    switch (el.type) {
      case 'D1': return `Диаметр D = ${p.D} м, Длина L = ${p.L} м`;
      case 'D2': return `Сечение ${p.A} × ${p.B} м, Длина L = ${p.L} м`;
      case 'O1': return `Диаметр D = ${p.D} м, Радиус R = ${p.R} м, Угол = ${p.A}°`;
      case 'O2': return `Сечение ${p.A} × ${p.B} м, Радиус R = ${p.R} м, Угол = ${p.A}°`;
      case 'A1': return `D1 = ${p.D1} м → D2 = ${p.D2} м, L = ${p.L} м`;
      case 'A3': return `Сечение ${p.A} × ${p.B} м → ${p.A1} × ${p.B1} м, Длина L = ${p.L} м`;
      case 'E1': return `Круг диаметром D = ${p.D} м`;
      case 'E2': return `Прямоугольник ${p.A} × ${p.B} м`;
      default: return JSON.stringify(p);
    }
  }

  const ELEMENT_DEFS = {
    D1: {
      name: 'Прямой круглый участок',
      icon: '⭕',
      fields: [
        { key: 'D', label: 'Диаметр воздуховода D', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'L', label: 'Длина участка L', unit: 'м', def: 4.0, step: 0.5 }
      ]
    },
    D2: {
      name: 'Прямой прямоугольный участок',
      icon: '⏹️',
      fields: [
        { key: 'A', label: 'Ширина стороны A', unit: 'м', def: 0.5, step: 0.05 },
        { key: 'B', label: 'Высота стороны B', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'L', label: 'Длина участка L', unit: 'м', def: 5.0, step: 0.5 }
      ]
    },
    O1: {
      name: 'Отвод круглый',
      icon: '↪️',
      fields: [
        { key: 'D', label: 'Диаметр воздуховода D', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'R', label: 'Радиус поворота R', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'A', label: 'Угол поворота α', unit: '°', def: 90, step: 15 }
      ]
    },
    O2: {
      name: 'Отвод прямоугольный',
      icon: '🔄',
      fields: [
        { key: 'A', label: 'Размер стороны A', unit: 'м', def: 0.5, step: 0.05 },
        { key: 'B', label: 'Размер стороны B', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'R', label: 'Радиус поворота R', unit: 'м', def: 0.5, step: 0.05 },
        { key: 'A', label: 'Угол поворота α', unit: '°', def: 90, step: 15 }
      ]
    },
    A1: {
      name: 'Переход круглый (D1 → D2)',
      icon: '🔻',
      fields: [
        { key: 'D1', label: 'Начальный диаметр D1', unit: 'м', def: 0.5, step: 0.05 },
        { key: 'D2', label: 'Конечный диаметр D2', unit: 'м', def: 0.3, step: 0.05 },
        { key: 'L', label: 'Длина перехода L', unit: 'м', def: 0.5, step: 0.1 }
      ]
    },
    A3: {
      name: 'Переход прямоугольный',
      icon: '🔻',
      fields: [
        { key: 'A', label: 'Начальная ширина A', unit: 'м', def: 0.6, step: 0.05 },
        { key: 'B', label: 'Начальная высота B', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'A1', label: 'Конечная ширина A1', unit: 'м', def: 0.4, step: 0.05 },
        { key: 'B1', label: 'Конечная высота B1', unit: 'м', def: 0.3, step: 0.05 },
        { key: 'L', label: 'Длина перехода L', unit: 'м', def: 0.5, step: 0.1 }
      ]
    },
    E1: {
      name: 'Заглушка круглая',
      icon: '🔵',
      fields: [
        { key: 'D', label: 'Диаметр заглушки D', unit: 'м', def: 0.4, step: 0.05 }
      ]
    },
    E2: {
      name: 'Заглушка прямоугольная',
      icon: '⬛',
      fields: [
        { key: 'A', label: 'Ширина заглушки A', unit: 'м', def: 0.5, step: 0.05 },
        { key: 'B', label: 'Высота заглушки B', unit: 'м', def: 0.4, step: 0.05 }
      ]
    }
  };

  function openAddElementDialog(type) {
    const config = ELEMENT_DEFS[type] || ELEMENT_DEFS.D1;
    state.activeElementType = type;

    const modal = document.getElementById('calcElementModal');
    const titleEl = document.getElementById('calcElementModalTitle');
    const typeNameEl = document.getElementById('elModalTypeName');
    const typeTagEl = document.getElementById('elModalTypeTag');
    const iconEl = document.getElementById('elModalIcon');
    const fieldsContainer = document.getElementById('elModalFieldsContainer');

    if (titleEl) titleEl.innerText = `Добавление элемента: ${config.name}`;
    if (typeNameEl) typeNameEl.innerText = config.name;
    if (typeTagEl) typeTagEl.innerText = type;
    if (iconEl) iconEl.innerText = config.icon;

    if (fieldsContainer) {
      fieldsContainer.innerHTML = '';
      config.fields.forEach(f => {
        const group = document.createElement('div');
        group.className = 'calc-form-group';
        group.innerHTML = `
          <label for="el_param_${f.key}">${f.label}</label>
          <div class="calc-field-wrap">
            <input type="number" id="el_param_${f.key}" data-key="${f.key}" class="calc-field-input calc-field-input--with-unit el-modal-input" value="${f.def}" step="${f.step || '0.1'}">
            <span class="calc-field-unit">${f.unit}</span>
          </div>
        `;
        fieldsContainer.appendChild(group);
      });

      fieldsContainer.querySelectorAll('.el-modal-input').forEach(inp => {
        inp.addEventListener('input', updateModalLiveArea);
      });
    }

    updateModalLiveArea();

    if (modal) {
      modal.classList.add('active');
    }
  }

  function getModalCurrentParams() {
    const type = state.activeElementType || 'D1';
    const config = ELEMENT_DEFS[type] || ELEMENT_DEFS.D1;
    const params = {};
    config.fields.forEach(f => {
      const el = document.getElementById(`el_param_${f.key}`);
      params[f.key] = el ? (parseFloat(el.value) || f.def) : f.def;
    });
    return params;
  }

  function updateModalLiveArea() {
    const type = state.activeElementType || 'D1';
    const params = getModalCurrentParams();
    const area = calculateArea(type, params);
    const areaEl = document.getElementById('elModalCalculatedArea');
    if (areaEl) {
      areaEl.innerHTML = `${area.toFixed(2)} <span class="unit">м²</span>`;
    }
  }

  window.calcEngineCloseElementModal = function() {
    const modal = document.getElementById('calcElementModal');
    if (modal) {
      modal.classList.remove('active');
    }
  };

  window.calcEngineSaveElement = function() {
    const type = state.activeElementType || 'D1';
    const config = ELEMENT_DEFS[type] || ELEMENT_DEFS.D1;
    const params = getModalCurrentParams();
    const s = calculateArea(type, params);

    state.block3Elements.push({
      id: Date.now(),
      type,
      name: config.name,
      params,
      s
    });

    renderBlock3Table();
    recalculateBlock3();
    window.calcEngineCloseElementModal();
  };

  function calculateArea(type, p) {
    const pi = Math.PI;
    switch (type) {
      case 'D1': return pi * (p.D || 0.4) * (p.L || 4.0);
      case 'D2': return 2 * ((p.A || 0.5) + (p.B || 0.4)) * (p.L || 5.0);
      case 'O1': return pi * (p.D || 0.4) * (pi * (p.R || 0.4) * ((p.A || 90) / 180));
      case 'O2': return 2 * ((p.A || 0.5) + (p.B || 0.4)) * (pi * (p.R || 0.5) * ((p.A || 90) / 180));
      case 'A1': {
        const d1 = p.D1 || 0.5;
        const d2 = p.D2 || 0.3;
        const l = p.L || 0.5;
        return pi * ((d1 + d2) / 2) * Math.sqrt(Math.pow(l, 2) + Math.pow((d1 - d2) / 2, 2));
      }
      case 'A3': {
        const a = p.A || 0.6;
        const b = p.B || 0.4;
        const a1 = p.A1 || 0.4;
        const b1 = p.B1 || 0.3;
        const l = p.L || 0.5;
        const h1 = Math.sqrt(Math.pow(l, 2) + Math.pow((b - b1)/2, 2));
        const h2 = Math.sqrt(Math.pow(l, 2) + Math.pow((a - a1)/2, 2));
        return (a + a1) * h1 + (b + b1) * h2;
      }
      case 'E1': return (pi * Math.pow(p.D || 0.4, 2)) / 4;
      case 'E2': return (p.A || 0.5) * (p.B || 0.4);
      default: return 1.0;
    }
  }

  function generateFanCurveSVG(La, Psa, Lpr, Psv) {
    const w = 560, h = 260;
    const padL = 65, padR = 35, padT = 30, padB = 45;
    const plotW = w - padL - padR;
    const plotH = h - padT - padB;

    const maxL = Math.max(35000, (Lpr || 27500) * 1.3, (La || 14500) * 1.3);
    const maxP = Math.max(1200, (Psv || 250) * 2.8, (Psa || 365) * 2.2);

    const scaleX = (l) => padL + (l / maxL) * plotW;
    const scaleY = (p) => padT + plotH - (p / maxP) * plotH;

    const curvePts = [];
    const steps = 30;
    for (let i = 0; i <= steps; i++) {
      const lVal = (maxL * 0.94 * i) / steps;
      const pVal = maxP * 0.88 * Math.max(0, 1 - Math.pow(lVal / (maxL * 0.9), 1.85));
      curvePts.push(`${scaleX(lVal).toFixed(1)},${scaleY(pVal).toFixed(1)}`);
    }
    const curvePath = 'M ' + curvePts.join(' L ');

    const opX = scaleX(La || 14500);
    const opY = scaleY(Psa || 365);

    return `
      <svg viewBox="0 0 ${w} ${h}" width="100%" height="auto" style="max-width:560px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:2px; font-family:'Segoe UI', Arial, sans-serif;">
        <!-- Axes -->
        <line x1="${padL}" y1="${padT}" x2="${padL}" y2="${padT + plotH}" stroke="#0f172a" stroke-width="1.5"/>
        <line x1="${padL}" y1="${padT + plotH}" x2="${padL + plotW}" y2="${padT + plotH}" stroke="#0f172a" stroke-width="1.5"/>
        
        <!-- Grid horizontal -->
        <line x1="${padL}" y1="${padT + plotH * 0.25}" x2="${padL + plotW}" y2="${padT + plotH * 0.25}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
        <line x1="${padL}" y1="${padT + plotH * 0.5}" x2="${padL + plotW}" y2="${padT + plotH * 0.5}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
        <line x1="${padL}" y1="${padT + plotH * 0.75}" x2="${padL + plotW}" y2="${padT + plotH * 0.75}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
        
        <!-- Grid vertical -->
        <line x1="${padL + plotW * 0.25}" y1="${padT}" x2="${padL + plotW * 0.25}" y2="${padT + plotH}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
        <line x1="${padL + plotW * 0.5}" y1="${padT}" x2="${padL + plotW * 0.5}" y2="${padT + plotH}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
        <line x1="${padL + plotW * 0.75}" y1="${padT}" x2="${padL + plotW * 0.75}" y2="${padT + plotH}" stroke="#e2e8f0" stroke-dasharray="3,3"/>

        <!-- Axis Labels -->
        <text x="${padL + plotW / 2}" y="${h - 10}" text-anchor="middle" font-size="11" fill="#0f172a" font-weight="600">Расход воздуха L, м³/ч</text>
        <text x="18" y="${padT + plotH / 2}" text-anchor="middle" font-size="11" fill="#0f172a" font-weight="600" transform="rotate(-90 18 ${padT + plotH / 2})">Давление Psv, Па</text>

        <!-- Fan curve -->
        <path d="${curvePath}" fill="none" stroke="#0284c7" stroke-width="2.5"/>

        <!-- Operating Point Dashes -->
        <line x1="${padL}" y1="${opY}" x2="${opX}" y2="${opY}" stroke="#ef4444" stroke-dasharray="4,3" stroke-width="1.5"/>
        <line x1="${opX}" y1="${opY}" x2="${opX}" y2="${padT + plotH}" stroke="#ef4444" stroke-dasharray="4,3" stroke-width="1.5"/>

        <!-- Operating Point Circle -->
        <circle cx="${opX}" cy="${opY}" r="5" fill="#ef4444" stroke="#ffffff" stroke-width="1.5"/>

        <!-- Tooltip / Badge -->
        <rect x="${Math.min(opX + 8, plotW - 20)}" y="${Math.max(padT + 5, opY - 32)}" width="148" height="34" rx="3" fill="#ffffff" stroke="#ef4444" stroke-width="1"/>
        <text x="${Math.min(opX + 14, plotW - 14)}" y="${Math.max(padT + 19, opY - 18)}" font-size="9.5" font-weight="bold" fill="#0f172a">Рабочая точка (Ta):</text>
        <text x="${Math.min(opX + 14, plotW - 14)}" y="${Math.max(padT + 31, opY - 6)}" font-size="9" fill="#475569">La = ${Math.round(La)} м³/ч, Psa = ${Math.round(Psa)} Па</text>
      </svg>
    `;
  }

  function generateProtocolHTML() {
    const meta = state.protocolMeta;

    if (state.currentBlock === 'block1') {
      const res = state.lastResults.block1 || {};
      const Lpr = res.Lpr || 27500;
      const Psv = res.Psv || 250;
      const Tpg = res.Tpg || 760;
      const Tpom = res.Tpom || 20;
      const Tv = res.Tv || (Tpg - 62);
      const Ta = res.Ta || (273 + Tpom);
      const rho_a = res.rho_a || (353 / Ta);
      const rho_sm = res.rho_sm || ((2 * rho_a * Ta) / (Tpg + Tv));
      const rho_v = res.rho_v || (353 / Tv);
      const h_top = res.h_top !== undefined ? res.h_top : 10.0;
      const h_bot = res.h_bot !== undefined ? res.h_bot : -1.0;
      const h = Math.max(0, h_top - h_bot);
      const Psa = res.Psa !== undefined ? res.Psa : 365.52;
      const La = res.La || Lpr;
      const Ga = parseFloat(res.Ga) || ((La * rho_a) / 3600);
      const G0 = parseFloat(res.G0) || (Ga * 0.95);
      const L0 = res.L0 || Math.round((G0 * 3600) / rho_a);
      const Lfact = res.Lfact ? Math.round(res.Lfact) : null;
      const deviation = res.deviation !== null && res.deviation !== undefined ? res.deviation : null;

      let table1Rows = '';
      const floors = res.floorResults || [];
      if (floors.length === 0) {
        table1Rows = '<tr><td colspan="11" style="padding:12px; color:#64748b;">Нет данных по закрытым клапанам</td></tr>';
      } else {
        floors.forEach((r, idx) => {
          table1Rows += `
            <tr>
              <td><b>${idx + 1}</b></td>
              <td>${r.kms || '0.4'}</td>
              <td>${r.lambda || '0.016'}</td>
              <td>${r.li || '3.0'}</td>
              <td>${r.de || '—'}</td>
              <td>${r.Fn || '—'}</td>
              <td>${r.Pn || '—'}</td>
              <td>${r.Ga_curr || '—'}</td>
              <td><b>${r.P_sn}</b></td>
              <td>${r.F_val || '—'}</td>
              <td><b>${r.G_leak}</b></td>
            </tr>
          `;
        });
      }

      const firstFloor = floors[0] || { P_sn: '198.1', G_leak: '0.081' };
      const sumGleak = floors.reduce((acc, f) => acc + (parseFloat(f.G_leak) || 0), 0);
      const chartSvg = generateFanCurveSVG(La, Psa, Lpr, Psv);

      let conclusionStatus = '';
      if (deviation !== null) {
        if (Math.abs(deviation) <= 15) {
          conclusionStatus = `3. <b>Система работает удовлетворительно.</b> Фактическое отклонение показателей расхода воздуха через открытое дымоприёмное устройство от расчётных нормативных составляет <b>${deviation > 0 ? '+' : ''}${deviation.toFixed(1)}%</b>, что находится в пределах нормативного допуска (не более ±15% по ГОСТ Р 53300-2009).`;
        } else {
          conclusionStatus = `3. <b>Система требует регулировки и наладки.</b> Фактическое отклонение показателей расхода воздуха через открытое дымоприёмное устройство составляет <b>${deviation > 0 ? '+' : ''}${deviation.toFixed(1)}%</b>, что превышает нормативный допуск ±15% по ГОСТ Р 53300-2009. Требуется балансировка шахты и наладка приводов клапанов.`;
        }
      } else {
        conclusionStatus = `3. <b>Система работает удовлетворительно.</b> Отклонение фактических показателей по расходу воздуха от определённых по расчёту допускается не более ±15%.`;
      }

      return `
        <div class="gost-sheet">
          <div class="gost-title-page">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:11px; color:#475569;">
              <div>Инв. № подп. _______ / Подп. и дата _______</div>
              <div class="gost-cipher-badge">${meta.number || '109.005/П-02'}</div>
              <div>Взам. инв. № _______ / Лист 1</div>
            </div>

            <div class="gost-object-box">
              <div style="font-size:16px; font-weight:bold; color:#0f172a; margin-bottom:4px;">${meta.objectName || 'Торговый центр «Академический»'}</div>
              <div style="font-size:13px; color:#475569;">${meta.address || 'СПб, Гражданский проспект, квартал 9А'}</div>
            </div>

            <h1 class="gost-doc-title">
              Расчётное определение значений требуемого расхода воздуха через открытое дымоприёмное устройство при приёмо-сдаточных и периодических испытаниях противодымной вентиляции
            </h1>

            <div class="gost-doc-subtitle">${meta.systemName || 'Система ДУ1'}</div>
            <div style="font-size:12px; color:#64748b; margin-top:8px;">Санкт-Петербург ${new Date().getFullYear()} г.</div>
          </div>

          <div class="gost-section-heading">Цель выполнения расчёта и нормативная база</div>
          <div style="font-size:12.5px; line-height:1.6; text-align:justify; color:#1e293b; margin-bottom:14px;">
            Целью выполнения расчёта является определение расхода воздуха для наиболее удалённого от вентилятора дымоприёмного устройства системы вытяжной противодымной вентиляции при фактической температуре воздуха в защищаемом помещении при проведении испытаний, так как оценка фактического расхода воздуха с проектными значениями не допускается требованиями <b>ГОСТ Р 53300-2009</b> «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний».<br>
            Методика проведения расчёта, изложенная в приложении Б <b>ГОСТ Р 53300-2009</b> «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний», прилагается.
          </div>

          <div class="gost-section-heading">1. Характеристики системы (по проектной документации и натурным замерам)</div>
          <div style="font-size:12.5px; line-height:1.6; color:#1e293b; margin-bottom:14px;">
            Наиболее удалённое дымоприёмное устройство от вентилятора расположено на отметке <b><i>H</i><sub>кл</sub> = ${h_bot.toFixed(3)} м</b> (${meta.section || 'цокольный этаж'}).
            <ul style="margin: 6px 0 10px 20px; padding: 0;">
              <li><i>L</i><sub>пр</sub> = <b>${Lpr.toLocaleString('ru-RU')}</b> м³/ч – проектный объёмный расход вентилятора,</li>
              <li><i>P</i><sub>sv</sub> = <b>${Psv}</b> Па – фактическое давление, создаваемое вентилятором,</li>
              <li><i>T</i><sub>пг</sub> = <b>${Tpg}</b> К – температура продуктов горения, удаляемых из помещения,</li>
              <li><i>T</i><sub>v</sub> = <b>${Tv}</b> К – температура продуктов горения, перемещаемых вентилятором,</li>
              <li><i>T</i><sub>a</sub> = <b>${Tpom} °C</b> = <b>${Ta} К</b> – температура воздуха в помещении на момент проведения испытаний,</li>
              <li><i>H</i><sub>уст</sub> = <b>${h_top.toFixed(3)}</b> м – высотная отметка установки вентилятора,</li>
              <li><i>H</i><sub>кл</sub> = <b>${h_bot.toFixed(3)}</b> м – высотная отметка расположения наиболее удалённого дымоприёмного устройства.</li>
            </ul>
          </div>

          <div class="gost-section-heading">2. Расчёт параметров газовоздушного тракта по методике ГОСТ Р 53300-2009</div>

          <!-- Formula (1) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">Среднюю плотность газа в вытяжном канале определяем по формуле (1):</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>ρ</i><sub>sm</sub> = 
                <span class="gost-frac">
                  <span class="gost-frac-top">2 · <i>ρ</i><sub>a</sub> · <i>T</i><sub>a</sub></span>
                  <span class="gost-frac-bot"><i>T</i><sub>пг</sub> + <i>T</i><sub>v</sub></span>
                </span>
                = 
                <span class="gost-frac">
                  <span class="gost-frac-top">2 · ${rho_a.toFixed(3)} · ${Ta}</span>
                  <span class="gost-frac-bot">${Tpg} + ${Tv}</span>
                </span>
                = <b>${rho_sm.toFixed(4)}</b> кг/м³
              </span>
              <span class="gost-formula-num">(1)</span>
            </div>
            <div class="gost-formula-note">
              где <i>ρ</i><sub>a</sub> = 
              <span class="gost-frac">
                <span class="gost-frac-top">353</span>
                <span class="gost-frac-bot"><i>T</i><sub>a</sub></span>
              </span>
              = 
              <span class="gost-frac">
                <span class="gost-frac-top">353</span>
                <span class="gost-frac-bot">${Ta}</span>
              </span>
              = <b>${rho_a.toFixed(3)}</b> кг/м³ – плотность воздуха при температуре ${Tpom} °C
            </div>
          </div>

          <!-- Formula (2) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">Вычисляем давление разрежения в вытяжном канале перед вентилятором по формуле (2):</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>P</i><sub>sa</sub> = 
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>P</i><sub>sv</sub> · <i>ρ</i><sub>v</sub></span>
                  <span class="gost-frac-bot">1,2</span>
                </span>
                + <i>g</i> · <i>h</i> · (<i>ρ</i><sub>a</sub> − <i>ρ</i><sub>sm</sub>) = 
                <span class="gost-frac">
                  <span class="gost-frac-top">${Psv} · ${rho_v.toFixed(3)}</span>
                  <span class="gost-frac-bot">1,2</span>
                </span>
                + 9,81 · ${h.toFixed(1)} · (${rho_a.toFixed(3)} − ${rho_sm.toFixed(4)}) = <b>${Psa.toFixed(2)}</b> Па
              </span>
              <span class="gost-formula-num">(2)</span>
            </div>
            <div class="gost-formula-note">
              где <i>ρ</i><sub>v</sub> = 
              <span class="gost-frac">
                <span class="gost-frac-top">353</span>
                <span class="gost-frac-bot"><i>T</i><sub>v</sub></span>
              </span>
              = 
              <span class="gost-frac">
                <span class="gost-frac-top">353</span>
                <span class="gost-frac-bot">${Tv}</span>
              </span>
              = <b>${rho_v.toFixed(3)}</b> кг/м³ – плотность перемещаемых дымовых газов при температуре <i>T</i><sub>v</sub>;<br>
              <i>h</i> = <i>H</i><sub>уст</sub> − <i>H</i><sub>кл</sub> = ${h_top.toFixed(1)} − (${h_bot.toFixed(1)}) = <b>${h.toFixed(1)}</b> м – разность уровней расположения вентилятора и открытого ДПУ.
            </div>
          </div>

          <!-- Formula (3) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формуле (3) вычисляем аэродинамическое соотношение для характеристики вентилятора:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>L</i><sub>a</sub> = <i>f</i>
                <span class="gost-bracket">(</span>
                <span class="gost-frac">
                  <span class="gost-frac-top">1,2 · <i>P</i><sub>sa</sub></span>
                  <span class="gost-frac-bot"><i>ρ</i><sub>v</sub></span>
                </span>
                <span class="gost-bracket">)</span>
                = <i>f</i>(${Math.round(1.2 * Psa / rho_v)})
              </span>
              <span class="gost-formula-num">(3)</span>
            </div>
            <div class="gost-formula-note">
              Используя аэродинамическую характеристику вентилятора (рис. 1), определяем значение объёмного расхода воздуха, перемещаемого им при температуре <i>T</i><sub>a</sub>:<br>
              <b><i>L</i><sub>a</sub> ≈ ${La.toLocaleString('ru-RU')} м³/ч</b>
            </div>
          </div>

          <!-- Formula (4) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формуле (4) определяем массовый расход воздуха перед вентилятором:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>G</i><sub>a</sub> = 
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>ρ</i><sub>a</sub> · <i>L</i><sub>a</sub></span>
                  <span class="gost-frac-bot">3600</span>
                </span>
                = 
                <span class="gost-frac">
                  <span class="gost-frac-top">${rho_a.toFixed(3)} · ${La}</span>
                  <span class="gost-frac-bot">3600</span>
                </span>
                = <b>${Ga.toFixed(3)}</b> кг/с
              </span>
              <span class="gost-formula-num">(4)</span>
            </div>
          </div>

          <!-- Formula (5) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формуле (5) определяем разрежение в вытяжном канале перед ближайшим к вентилятору закрытым противопожарным клапаном:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>P</i><sub>sn</sub> = <i>P</i><sub>sa</sub> − 0,5 · <i>ρ</i><sub>a</sub> · 
                <span class="gost-bracket">(</span>
                ΣКМС + 
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>λ</i><sub>n</sub> · <i>l</i><sub>n</sub></span>
                  <span class="gost-frac-bot"><i>d</i><sub>en</sub></span>
                </span>
                <span class="gost-bracket">)</span>
                · 
                <span class="gost-bracket">(</span>
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>G</i><sub>a</sub></span>
                  <span class="gost-frac-bot"><i>ρ</i><sub>a</sub> · <i>F</i><sub>n</sub></span>
                </span>
                <span class="gost-bracket">)</span><sup>2</sup>
                = <b>${firstFloor.P_sn}</b> Па
              </span>
              <span class="gost-formula-num">(5)</span>
            </div>
          </div>

          <!-- Formula (6) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формуле (6) вычисляем подсос воздуха через ближайшее к вентилятору закрытое дымоприёмное устройство:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                Δ<i>G</i><sub>dpn</sub> = <i>F</i><sub>dpn</sub> · 
                <span class="gost-bracket">(</span>
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>P</i><sub>sn</sub></span>
                  <span class="gost-frac-bot"><i>S</i><sub>dpn</sub></span>
                </span>
                <span class="gost-bracket">)</span><sup>0,5</sup>
                = <b>${firstFloor.G_leak}</b> кг/с
              </span>
              <span class="gost-formula-num">(6)</span>
            </div>
          </div>

          <!-- Formula (7) and (8) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формулам (7) и (8) определяем разрежение и подсос воздуха у каждого последующего <i>i</i>-го закрытого клапана при температуре <i>T</i><sub>a</sub>:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>P</i><sub>si</sub> = <i>P</i><sub>si-1</sub> − 0,5 · <i>ρ</i><sub>a</sub> · 
                <span class="gost-bracket">(</span>
                ΣКМС + 
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>λ</i><sub>i</sub> · <i>l</i><sub>i</sub></span>
                  <span class="gost-frac-bot"><i>d</i><sub>ei</sub></span>
                </span>
                <span class="gost-bracket">)</span>
                · 
                <span class="gost-bracket">(</span>
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>G</i><sub>i</sub></span>
                  <span class="gost-frac-bot"><i>ρ</i><sub>a</sub> · <i>F</i><sub>i</sub></span>
                </span>
                <span class="gost-bracket">)</span><sup>2</sup>
              </span>
              <span class="gost-formula-num">(7)</span>
            </div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                Δ<i>G</i><sub>dpi</sub> = <i>F</i><sub>dpi</sub> · 
                <span class="gost-bracket">(</span>
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>P</i><sub>si</sub></span>
                  <span class="gost-frac-bot"><i>S</i><sub>dpi</sub></span>
                </span>
                <span class="gost-bracket">)</span><sup>0,5</sup>
              </span>
              <span class="gost-formula-num">(8)</span>
            </div>
            <div class="gost-formula-note">
              <i>λ</i><sub>n</sub> (<i>λ</i><sub>i</sub>) = 0,016 – коэффициент гидравлического сопротивления трения вытяжного канала;<br>
              <i>l</i><sub>n</sub> (<i>l</i><sub>i</sub>) – длина участка вытяжного канала, принимается по проектным данным, м;<br>
              <i>d</i><sub>en</sub> (<i>d</i><sub>ei</sub>) = 
              <span class="gost-frac">
                <span class="gost-frac-top">4 · <i>F</i></span>
                <span class="gost-frac-bot"><i>P</i></span>
              </span>
              – эквивалентный гидравлический диаметр вытяжного канала, м;<br>
              <i>F</i> – площадь проходного сечения вытяжного канала, м²; <i>P</i> – периметр сечения вытяжного канала, м;<br>
              <i>F</i><sub>dpn</sub> (<i>F</i><sub>dpi</sub>) – площадь проходного сечения закрытого противопожарного клапана, м²;<br>
              <i>S</i><sub>dpn</sub> (<i>S</i><sub>dpi</sub>) = 10 000 м³/кг – удельное сопротивление воздухопроницанию закрытого клапана.
            </div>
          </div>

          <!-- Table 1 -->
          <div class="gost-table-title">Расчёт параметров <i>P</i><sub>sn</sub>, Δ<i>G</i><sub>dpn</sub> сведён в таблицу 1:</div>
          <table class="gost-grid-table">
            <thead>
              <tr>
                <th>№ клапана по удалению от вентилятора</th>
                <th>КМС</th>
                <th><i>λ</i><sub>n</sub> (<i>λ</i><sub>i</sub>)</th>
                <th><i>l</i><sub>n</sub> (<i>l</i><sub>i</sub>), м</th>
                <th><i>d</i><sub>en</sub>, м</th>
                <th><i>F</i><sub>n</sub> (<i>F</i><sub>i</sub>), м²</th>
                <th><i>P</i><sub>n</sub> (<i>P</i><sub>i</sub>), м</th>
                <th><i>G</i><sub>a</sub> (<i>G</i><sub>i</sub>), кг/с</th>
                <th><i>P</i><sub>sn</sub> (<i>P</i><sub>si</sub>), Па</th>
                <th>Площадь клапана <i>F</i><sub>dpn</sub>, м²</th>
                <th>Δ<i>G</i><sub>dpn</sub>, кг/с</th>
              </tr>
            </thead>
            <tbody>
              ${table1Rows}
            </tbody>
          </table>

          <!-- Formula (9) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">По формуле (9) вычисляем массовый расход воздуха, удаляемого через открытое дымоприёмное устройство:</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>G</i><sub>0</sub> = <i>G</i><sub>a</sub> − (Δ<i>G</i><sub>dpn</sub> + ΣΔ<i>G</i><sub>dpi</sub>) = ${Ga.toFixed(3)} − ${sumGleak.toFixed(4)} = <b>${G0.toFixed(3)}</b> кг/с
              </span>
              <span class="gost-formula-num">(9)</span>
            </div>
          </div>

          <!-- Formula (10) -->
          <div class="gost-formula-block">
            <div class="gost-formula-text">Требуемое значение расхода воздуха через открытое дымоприёмное устройство испытываемой системы вытяжной противодымной вентиляции определяем по формуле (10):</div>
            <div class="gost-formula-row">
              <span class="gost-formula-math">
                <i>L</i><sub>0</sub> = 
                <span class="gost-frac">
                  <span class="gost-frac-top">3600 · <i>G</i><sub>0</sub></span>
                  <span class="gost-frac-bot"><i>ρ</i><sub>a</sub></span>
                </span>
                = 
                <span class="gost-frac">
                  <span class="gost-frac-top">3600 · ${G0.toFixed(3)}</span>
                  <span class="gost-frac-bot">${rho_a.toFixed(3)}</span>
                </span>
                = <b style="font-size:15px; color:#167b88;">${Math.round(L0).toLocaleString('ru-RU')}</b> м³/ч
              </span>
              <span class="gost-formula-num">(10)</span>
            </div>
          </div>

          <!-- Fact compare -->
          <div class="gost-fact-compare">
            ${Lfact ? `
              Фактический расход воздуха через наиболее удалённое от вентилятора открытое дымоприёмное устройство: <b><i>L</i><sub>ф</sub> = ${Math.round(Lfact).toLocaleString('ru-RU')} м³/ч</b>.<br>
              Отклонение фактического расхода от расчетного: 
              <b><i>δ</i> = 
                <span class="gost-frac">
                  <span class="gost-frac-top"><i>L</i><sub>ф</sub> − <i>L</i><sub>0</sub></span>
                  <span class="gost-frac-bot"><i>L</i><sub>0</sub></span>
                </span>
                · 100% = 
                <span class="gost-frac">
                  <span class="gost-frac-top">${Math.round(Lfact)} − ${Math.round(L0)}</span>
                  <span class="gost-frac-bot">${Math.round(L0)}</span>
                </span>
                · 100% = ${deviation > 0 ? '+' : ''}${deviation.toFixed(1)}%</b>
            ` : `
              Фактический расход воздуха через наиболее удалённое от вентилятора открытое дымоприёмное устройство: <b><i>L</i><sub>ф</sub> = _______ м³/ч</b>.<br>
              <i>(Значение определяется по показаниям поверенного термоанемометра в сечении открытого клапана).</i>
            `}
          </div>

          <!-- Conclusions -->
          <div class="gost-conclusions">
            <h4 style="margin: 0 0 10px; font-size: 13px; text-transform: uppercase;">Выводы:</h4>
            <ol style="margin: 0; padding-left: 20px; line-height: 1.65; font-size: 12.5px;">
              <li>В соответствии с <b>ГОСТ Р 53300-2009</b> «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний», определено требуемое значение расхода воздуха через открытое дымоприёмное устройство для систем противодымной вытяжной вентиляции: <b><i>L</i><sub>0</sub> = ${Math.round(L0).toLocaleString('ru-RU')} м³/ч</b>.</li>
              <li>В соответствии с <b>ГОСТ Р 53300-2009</b> отклонение фактических показателей по расходу воздуха от определённых по расчёту допускается <b>не более 15%</b>.</li>
              <li>${conclusionStatus}</li>
            </ol>
          </div>

          <!-- Figure 1 SVG Fan curve -->
          <div class="gost-chart-wrapper">
            ${chartSvg}
            <div class="gost-chart-caption">
              Рисунок 1. Аэродинамическая характеристика вентилятора системы ${meta.systemName || 'ДУ1'}
            </div>
          </div>

          <!-- Title block / Stamp Table -->
          <table class="gost-stamp-table">
            <tr>
              <td style="width:10%;" class="gost-stamp-label">Изм.</td>
              <td style="width:10%;">1</td>
              <td style="width:15%;" class="gost-stamp-label">Шифр проекта:</td>
              <td colspan="2"><b>${meta.number || '109.005/П-02'}</b></td>
              <td rowspan="4" style="width:32%; text-align:center; vertical-align:middle; background:#f8fafc;">
                <div style="font-weight:bold; font-size:12px; text-transform:uppercase;">ООО «Баланс Инженерных Систем»</div>
                <div style="font-size:10px; color:#475569; margin-top:2px;">Испытательная лаборатория | biscorp.ru</div>
                <div style="margin-top:8px; font-size:10.5px; border:1px dashed #94a3b8; display:inline-block; padding:2px 10px;">М.П.</div>
              </td>
            </tr>
            <tr>
              <td class="gost-stamp-label">Разраб.</td>
              <td>${meta.engineer || 'Иванов И.И.'}</td>
              <td class="gost-stamp-label">Объект:</td>
              <td colspan="2">${meta.objectName || 'ТК «Академический»'}</td>
            </tr>
            <tr>
              <td class="gost-stamp-label">Пров.</td>
              <td>${meta.approver || 'Петров П.П.'}</td>
              <td class="gost-stamp-label">Система:</td>
              <td colspan="2"><b>${meta.systemName || 'Система ДУ1'}</b></td>
            </tr>
            <tr>
              <td class="gost-stamp-label">Утв.</td>
              <td>${meta.approver || 'Петров П.П.'}</td>
              <td class="gost-stamp-label">Стадия / Лист:</td>
              <td style="width:15%;">И / Лист 1</td>
              <td style="width:18%;">Дата: ${meta.date}</td>
            </tr>
          </table>
        </div>
      `;
    }

    // Fallback for Block 2 (AVOK)
    const res = state.lastResults.avok || {};
    const protocolTitle = `ПРОТОКОЛ РАСЧЕТА СИСТЕМЫ ПРОТИВОДЫМНОЙ ВЕНТИЛЯЦИИ (${res.type || 'АВОК'})`;
    
    let detailsRows = '';
    (res.details || []).forEach(d => {
      detailsRows += `<tr><td class="field-name">${d.label}:</td><td><b>${d.val}</b></td></tr>`;
    });

    const tableHtml = `
      <table class="protocol-table-info">
        <tr><td class="field-name">Объект / Адрес:</td><td>${meta.objectName} (${meta.address || ''})</td><td class="field-name">Дата расчета:</td><td>${meta.date}</td></tr>
        <tr><td class="field-name">Наименование системы:</td><td>${meta.systemName}</td><td class="field-name">Номер протокола:</td><td>№ ${meta.number}</td></tr>
        <tr><td class="field-name">Нормативная база:</td><td>Рекомендации АВОК 5.5.1</td><td class="field-name">Расчетчик:</td><td>${meta.engineer}</td></tr>
      </table>
      <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Результаты расчета параметров противодымной вентиляции:</h4>
      <table class="protocol-table-info">
        <tr><td class="field-name">${res.mainLabel || 'Расход воздуха'}:</td><td><b style="font-size:14px;">${res.mainVal || '—'}</b></td></tr>
        <tr><td class="field-name">${res.sub1Label || 'Давление'}:</td><td><b>${res.sub1Val || '—'}</b></td></tr>
        <tr><td class="field-name">${res.sub2Label || 'Показатель'}:</td><td><b>${res.sub2Val || '—'}</b></td></tr>
        ${detailsRows}
      </table>
    `;
    const conclusionText = `Заключение: Расчетные параметры системы противодымной вентиляции ${meta.systemName} соответствуют требованиям нормативов АВОК и СП 7.13130.`;

    return `
      <div class="protocol-sheet-container">
        <div class="protocol-sheet-header">
          <div class="protocol-org-name">
            ООО «Баланс Инженерных Систем»
            <div class="protocol-org-sub">ИНН: 7700000000 | biscorp.ru | office@bis-rf.ru</div>
          </div>
          <div class="protocol-stamp-box">
            <b>УТВЕРЖДАЮ:</b><br>
            Руководитель лаборатории<br>
            <div class="sign-underline" style="width:140px; margin:4px 0 2px auto;"></div>
            <span>/ ${meta.approver} /</span>
          </div>
        </div>

        <div class="protocol-main-title">${protocolTitle}</div>
        <div class="protocol-number-date">Протокол № ${meta.number} от ${meta.date}</div>

        ${tableHtml}

        <div class="protocol-conclusion-box">
          ${conclusionText}
        </div>

        <div class="protocol-signs-row">
          <div class="sign-column">
            <span>Протокол составил инженер-испытатель:</span>
            <div class="sign-underline"></div>
            <span>/ ${meta.engineer} /</span>
          </div>
          <div class="sign-column">
            <span>Представитель заказчика / технадзора:</span>
            <div class="sign-underline"></div>
            <span>/ __________________________ /</span>
          </div>
        </div>
      </div>
    `;
  }

  window.calcEngineOpenProtocol = function () {
    recalculateCurrent();
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
    if (dom.protocolModal) {
      dom.protocolModal.classList.add('active');
    }
  };

  window.calcEngineCloseProtocol = function () {
    if (dom.protocolModal) {
      dom.protocolModal.classList.remove('active');
    }
  };

  window.calcEngineUpdateMeta = function (field, val) {
    state.protocolMeta[field] = val;
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
  };

  window.calcEngineDirectPrint = function () {
    recalculateCurrent();
    const bodyContent = generateProtocolHTML();
    const printWindow = window.open('', '_blank', 'width=900,height=750');
    if (!printWindow) {
      window.print();
      return;
    }

    printWindow.document.open();
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Протокол испытаний БИС</title>
        <style>
          @page { size: A4 portrait; margin: 10mm 12mm 12mm 12mm; }
          body { font-family: 'Times New Roman', 'Cambria Math', 'Calibri', Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 0; background: #fff; }
          .protocol-sheet-container { padding: 0; }
          .protocol-sheet-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 10px; }
          .protocol-org-name { font-size: 15px; font-weight: bold; text-transform: uppercase; }
          .protocol-org-sub { font-size: 11px; color: #555; margin-top: 2px; }
          .protocol-stamp-box { text-align: right; font-size: 11px; }
          .protocol-main-title { text-align: center; font-size: 16px; font-weight: bold; margin: 14px 0 4px; text-transform: uppercase; }
          .protocol-number-date { text-align: center; font-size: 12px; margin-bottom: 16px; color: #333; }
          .protocol-table-info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
          .protocol-table-info td { padding: 5px 8px; border: 1px solid #000; font-size: 11px; }
          .protocol-table-info td.field-name { font-weight: bold; background: #f4f4f4; width: 30%; }
          .protocol-grid-data { width: 100%; border-collapse: collapse; margin: 12px 0; }
          .protocol-grid-data th, .protocol-grid-data td { border: 1px solid #000; padding: 5px 6px; font-size: 10.5px; text-align: center; }
          .protocol-grid-data th { background: #f4f4f4; font-weight: bold; }
          .protocol-conclusion-box { margin: 16px 0; padding: 10px 12px; border: 2px solid #000; background: #fafafa; font-weight: bold; font-size: 12px; }
          .protocol-signs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
          .sign-column { display: flex; flex-direction: column; gap: 4px; font-size: 11px; }
          .sign-underline { border-bottom: 1px solid #000; height: 20px; }

          /* GOST sheet print styles */
          .gost-sheet { padding: 0; max-width: 100%; border: none; box-shadow: none; margin: 0; }
          .gost-title-page { text-align: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #000; }
          .gost-cipher-badge { display: inline-block; font-family: 'Consolas', monospace; font-size: 13px; font-weight: 700; padding: 3px 12px; border: 1.5px solid #000; letter-spacing: 0.1em; margin-bottom: 10px; }
          .gost-doc-title { font-size: 14px; font-weight: 800; text-transform: uppercase; line-height: 1.35; margin: 10px auto; max-width: 650px; }
          .gost-doc-subtitle { font-size: 13px; font-weight: 700; margin: 6px 0 4px; }
          .gost-object-box { margin: 10px 0; font-size: 12px; color: #334155; }
          .gost-section-heading { font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 18px 0 8px; padding-bottom: 3px; border-bottom: 1.5px solid #000; }
          .gost-formula-block { margin: 10px 0 14px; padding: 6px 10px; background: #fafafa; border-left: 3px solid #167b88; page-break-inside: avoid; }
          .gost-formula-text { font-size: 12px; margin-bottom: 4px; }
          .gost-formula-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin: 6px 0; }
          .gost-formula-math { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 3px; font-family: 'Cambria Math', 'Times New Roman', serif; font-style: italic; }
          .gost-formula-math b, .gost-formula-math strong { font-style: normal; }
          .gost-formula-num { font-family: 'Times New Roman', serif; font-style: normal; font-weight: 700; font-size: 12.5px; margin-left: 16px; white-space: nowrap; }
          .gost-formula-note { font-size: 11px; line-height: 1.45; color: #475569; margin-top: 4px; }
          .gost-frac { display: inline-flex; flex-direction: column; vertical-align: middle; text-align: center; margin: 0 4px; font-size: 0.9em; }
          .gost-frac-top { border-bottom: 1.5px solid #000; padding: 0 4px 1px; line-height: 1.1; }
          .gost-frac-bot { padding: 1px 4px 0; line-height: 1.1; }
          .gost-bracket { font-size: 1.3em; line-height: 1; vertical-align: middle; font-style: normal; }
          .gost-table-title { font-size: 11.5px; font-weight: 700; margin: 14px 0 6px; }
          .gost-grid-table { width: 100%; border-collapse: collapse; margin: 8px 0 14px; font-size: 10.5px; page-break-inside: avoid; }
          .gost-grid-table th, .gost-grid-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; line-height: 1.2; }
          .gost-grid-table th { background: #f1f5f9; font-weight: 700; }
          .gost-fact-compare { margin: 14px 0; padding: 10px 14px; border: 1.5px dashed #0284c7; background: #f0f9ff; font-size: 12px; line-height: 1.5; page-break-inside: avoid; }
          .gost-conclusions { margin: 16px 0; padding: 12px 16px; border: 2px solid #000; background: #fff; page-break-inside: avoid; }
          .gost-chart-wrapper { margin: 16px auto; text-align: center; max-width: 540px; page-break-inside: avoid; }
          .gost-chart-caption { font-size: 11.5px; font-weight: 600; color: #334155; margin-top: 6px; }
          .gost-stamp-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10.5px; border: 2px solid #000; page-break-inside: avoid; }
          .gost-stamp-table td { border: 1px solid #000; padding: 3px 5px; vertical-align: middle; }
          .gost-stamp-label { font-weight: 600; color: #475569; }
        </style>
      </head>
      <body>
        ${bodyContent}
        <script>
          window.onload = function() {
            window.focus();
            window.print();
            setTimeout(function() { window.close(); }, 1000);
          };
        <\/script>
      </body>
      </html>
    `);
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
