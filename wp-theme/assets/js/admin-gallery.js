document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-gallery-upload]');
  if (!root) return;
  const status = root.querySelector('[data-upload-status]');
  const progress = root.querySelector('[data-upload-progress]');
  const inputs = [...root.querySelectorAll('input[type="file"]')];
  let busy = false;
  window.addEventListener('beforeunload', event => { if (busy) { event.preventDefault(); event.returnValue = ''; } });
  const request = async (action, data) => {
    const body = new FormData();
    Object.entries({action, nonce:root.dataset.nonce, post_id:root.dataset.postId, ...data}).forEach(([key,value]) => body.append(key,value));
    const response = await fetch(window.ajaxurl, {method:'POST', body});
    const result = await response.json();
    if (!result.success) throw new Error(result.data?.message || 'Ошибка загрузки.');
    return result.data;
  };
  const put = (url, file) => new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('PUT', url);
    xhr.setRequestHeader('Content-Type', file.type);
    xhr.upload.onprogress = e => { if (e.lengthComputable) progress.value = Math.round(e.loaded / e.total * 100); };
    xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? resolve() : reject(new Error('S3 отклонил файл. Проверьте ключи и права бакета.'));
    xhr.onerror = () => reject(new Error('Не удалось загрузить файл. Проверьте подключение и CORS в настройках S3.'));
    xhr.send(file);
  });
  inputs.forEach(input => input.addEventListener('change', async () => {
    const file = input.files[0]; if (!file || busy) return;
    busy = true; inputs.forEach(el => { el.disabled = true; }); progress.hidden = false; progress.value = 0;
    if (window.wp?.data?.dispatch) window.wp.data.dispatch('core/editor')?.lockPostSaving('bis-gallery-upload');
    status.textContent = 'Загрузка в S3… Не закрывайте страницу.';
    try {
      const signed = await request('bis_gallery_sign_upload', {name:file.name,mime:file.type,size:file.size,slot:input.dataset.uploadSlot});
      await put(signed.upload_url, file);
      const result = await request('bis_gallery_complete_upload', {ticket:signed.ticket});
      root.querySelector(`[data-upload-result="${input.dataset.uploadSlot}"]`).value = result.url;
      status.textContent = 'Файл загружен. Сохраните или опубликуйте материал.';
    } catch (error) { status.textContent = error.message; }
    finally {
      busy = false; inputs.forEach(el => { el.disabled = false; });
      if (window.wp?.data?.dispatch) window.wp.data.dispatch('core/editor')?.unlockPostSaving('bis-gallery-upload');
    }
  }));
});
