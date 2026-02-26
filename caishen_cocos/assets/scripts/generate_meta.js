const fs = require('fs');
const path = require('path');

const imageFiles = [
  'altar.png',
  'bg_caishen.png',
  'btn_burn.png',
  'btn_close.png',
  'caishen.png',
  'coin.png',
  'incense_burner.png',
  'incense_lit.png',
  'incense_unlit.png',
  'popup_bg.png',
  'qrcode.png',
  'share_timeline.png'
];

const audioFiles = [
  'bgm_temple.mp3',
  'bgm_temple.wav',
  'burn_sound.wav',
  'drop.mp3'
];

function generateUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

const imagesDir = path.join(__dirname, '../resources/images/caishen');
const audioDir = path.join(__dirname, '../resources/audio/caishen');

imageFiles.forEach(file => {
  const meta = {
    ver: '1.0.24',
    uuid: generateUUID(),
    type: 'sprite-frame',
    subMetas: {}
  };
  fs.writeFileSync(
    path.join(imagesDir, file + '.meta'),
    JSON.stringify(meta, null, 2)
  );
  console.log(`Created meta for ${file}`);
});

audioFiles.forEach(file => {
  const meta = {
    ver: '1.0.24',
    uuid: generateUUID(),
    type: 'audio-clip',
    subMetas: {}
  };
  fs.writeFileSync(
    path.join(audioDir, file + '.meta'),
    JSON.stringify(meta, null, 2)
  );
  console.log(`Created meta for ${file}`);
});

console.log('All meta files generated!');
