<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$m = ['type' => (($_GET['type'] ?? '') === 'video' ? 'video' : 'music'), 'title' => '', 'artist' => '', 'album' => '', 'year' => '', 'genre' => '', 'language' => 'English', 'category_id' => '', 'description' => '', 'file_path' => '', 'cover_image' => '', 'duration' => 0];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM media WHERE id=?'); $s->execute([$id]); $m = $s->fetch();
    if (!$m) exit('Media not found.');
}
$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $error = 'The file is larger than the PHP upload limit (' . ini_get('post_max_size') . '). Raise upload_max_filesize and post_max_size in php.ini (XAMPP > Config > php.ini), restart Apache, and try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type  = $_POST['type'] ?? 'music';
    $title = trim($_POST['title'] ?? '');
    $file  = $m['file_path'];
    $cover = $m['cover_image'];
    $cat   = (int)($_POST['category_id'] ?? 0);
    if (!in_array($type, ['music', 'video'], true) || !$title) $error = 'Title and media type are required.';
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!$error && isset($_FILES['media_file']) && in_array($_FILES['media_file']['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true))
        $error = 'The media file is larger than the PHP upload limit (' . ini_get('upload_max_filesize') . '). Raise upload_max_filesize and post_max_size in php.ini, restart Apache, and try again.';

    if (!$error && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'], true)) $type = 'music';        // audio file is always a song
        elseif (in_array($ext, ['mp4', 'webm', 'mov', 'm4v'], true)) $type = 'video';
        $allowed = $type === 'video' ? ['mp4', 'webm', 'mov', 'm4v'] : ['mp3', 'wav', 'ogg', 'm4a'];
        if (!in_array($ext, $allowed, true)) $error = $type === 'video' ? 'Video must be MP4, WEBM, MOV or M4V.' : 'Music must be MP3, WAV, OGG or M4A.';
        elseif ($_FILES['media_file']['size'] > 200 * 1024 * 1024) $error = 'Media file must be 200 MB or smaller.';
        else {
            $name = bin2hex(random_bytes(12)) . '.' . $ext;
            if (move_uploaded_file($_FILES['media_file']['tmp_name'], $dir . '/' . $name)) $file = '../uploads/' . $name; else $error = 'Could not save media file.';
        }
    }
    if (!$error && isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $error = 'Cover must be JPG, PNG or WEBP.';
        else {
            $name = bin2hex(random_bytes(12)) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_file']['tmp_name'], $dir . '/' . $name)) $cover = '../uploads/' . $name; else $error = 'Could not save cover image.';
        }
    }
    if (!$error && !$file) $error = 'Please upload the media file.';

    if (!$error) {
        $data = [$type, $title, trim($_POST['artist'] ?? ''), trim($_POST['album'] ?? ''), ($_POST['year'] ?? '') !== '' ? (int)$_POST['year'] : null, trim($_POST['genre'] ?? ''), trim($_POST['language'] ?? ''), $cat ?: null, trim($_POST['description'] ?? ''), $file, $cover, max(0, (int)($_POST['duration'] ?? 0))];
        if ($id) {
            $data[] = $id;
            $sql = 'UPDATE media SET type=?,title=?,artist=?,album=?,year=?,genre=?,language=?,category_id=?,description=?,file_path=?,cover_image=?,duration=? WHERE id=?';
        } else {
            $sql = 'INSERT INTO media(type,title,artist,album,year,genre,language,category_id,description,file_path,cover_image,duration) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)';
        }
        $pdo->prepare($sql)->execute($data);
        flash('success', $id ? 'Media updated successfully.' : 'Media added successfully. It is now live on the website.');
        header('Location: media.php'); exit;
    }
    /* keep what the admin typed when showing the error */
    foreach (['type', 'title', 'artist', 'album', 'year', 'genre', 'language', 'description'] as $k) if (isset($_POST[$k])) $m[$k] = $_POST[$k];
    $m['category_id'] = $cat ?: '';
}

$pageTitle = $id ? 'Edit Media' : 'Add Music / Video';
$pageSub   = 'Upload a song or MP4 video with artwork. It appears on the user website after saving.';
$crumb     = 'Library';
require 'header.php';
?>
<?php
/* Language list for the Language dropdown (stored as plain text in media.language - no database change) */
$popularLangs = ['Urdu','Punjabi','Sindhi','Pashto','Balochi','Saraiki','Hindko','Kashmiri','Brahui','Hindi','English','Arabic','Persian (Farsi)','Bengali','Turkish'];
$allLangs = ['Afrikaans','Albanian','Amharic','Armenian','Assamese','Aymara','Azerbaijani','Basque','Belarusian','Bhojpuri','Bosnian','Bulgarian','Burmese','Cantonese','Catalan','Cebuano','Chinese (Mandarin)','Corsican','Croatian','Czech','Danish','Dari','Dhivehi (Maldivian)','Dogri','Dutch','Esperanto','Estonian','Ewe','Filipino (Tagalog)','Finnish','French','Frisian','Galician','Georgian','German','Greek','Guarani','Gujarati','Haitian Creole','Hausa','Hawaiian','Hebrew','Hmong','Hungarian','Icelandic','Igbo','Ilocano','Indonesian','Irish','Italian','Japanese','Javanese','Kannada','Kazakh','Khmer','Kinyarwanda','Konkani','Korean','Krio','Kurdish (Kurmanji)','Kurdish (Sorani)','Kyrgyz','Lao','Latin','Latvian','Lingala','Lithuanian','Luganda','Luxembourgish','Macedonian','Maithili','Malagasy','Malay','Malayalam','Maltese','Maori','Marathi','Meiteilon (Manipuri)','Mizo','Mongolian','Nepali','Norwegian','Nyanja (Chichewa)','Odia (Oriya)','Oromo','Polish','Portuguese','Quechua','Romanian','Russian','Samoan','Sanskrit','Scots Gaelic','Sepedi','Serbian','Sesotho','Shona','Sinhala','Slovak','Slovenian','Somali','Spanish','Sundanese','Swahili','Swedish','Tajik','Tamil','Tatar','Telugu','Thai','Tigrinya','Tsonga','Turkmen','Twi (Akan)','Ukrainian','Uyghur','Uzbek','Vietnamese','Welsh','Wolof','Xhosa','Yiddish','Yoruba','Zulu','Instrumental (No Vocals)','Multi-language','Other'];
$popularSet = array_flip($popularLangs);
$allLangs = array_values(array_filter($allLangs, function($l) use ($popularSet) { return !isset($popularSet[$l]); }));
sort($allLangs, SORT_NATURAL | SORT_FLAG_CASE);
$curLang = trim((string)($m['language'] ?? ''));
$knownLang = in_array($curLang, $popularLangs, true) || in_array($curLang, $allLangs, true);
?>
    <?php if ($error): ?>
    <div class="alert-custom alert-custom-danger mb-4">
      <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
      <div class="alert-custom-content"><?= h($error) ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card border-light shadow-sm p-4 mb-4">
      <?= csrf_field() ?>
      <input type="hidden" name="duration" id="duration" value="<?= h($m['duration'] ?? 0) ?>">
      <div class="sg-form-grid">

        <div class="sg-section"><b>01</b><div><strong>Media details</strong><small>Tell listeners what they are about to discover.</small></div></div>

        <div>
          <label class="form-label-custom" for="f-type">Media Type *</label>
          <select class="form-select-custom" id="f-type" name="type">
            <option value="music" <?= $m['type'] === 'music' ? 'selected' : '' ?>>Music / Song</option>
            <option value="video" <?= $m['type'] === 'video' ? 'selected' : '' ?>>Video / MP4</option>
          </select>
        </div>
        <div>
          <label class="form-label-custom" for="f-title">Title *</label>
          <input class="form-control-custom" id="f-title" name="title" value="<?= h($m['title']) ?>" required>
        </div>
        <div>
          <label class="form-label-custom" for="f-artist">Artist</label>
          <input class="form-control-custom" id="f-artist" name="artist" value="<?= h($m['artist']) ?>">
        </div>
        <div>
          <label class="form-label-custom" for="f-album">Album</label>
          <input class="form-control-custom" id="f-album" name="album" value="<?= h($m['album']) ?>">
        </div>
        <div>
          <label class="form-label-custom" for="f-year">Year</label>
          <input class="form-control-custom" id="f-year" type="number" name="year" min="1900" max="2100" value="<?= h($m['year']) ?>">
        </div>
        <div>
          <label class="form-label-custom" for="f-genre">Genre</label>
          <input class="form-control-custom" id="f-genre" name="genre" placeholder="Pop, Qawwali, Folk..." value="<?= h($m['genre']) ?>">
        </div>
        <div>
          <label class="form-label-custom" for="f-lang">Language</label>
          <select class="form-select-custom" id="f-lang" name="language">
            <option value="">Select language</option>
            <?php if ($curLang !== '' && !$knownLang): ?><option value="<?= h($curLang) ?>" selected><?= h($curLang) ?></option><?php endif; ?>
            <optgroup label="Popular">
              <?php foreach ($popularLangs as $l): ?><option value="<?= h($l) ?>" <?= $curLang === $l ? 'selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?>
            </optgroup>
            <optgroup label="All languages (A - Z)">
              <?php foreach ($allLangs as $l): ?><option value="<?= h($l) ?>" <?= $curLang === $l ? 'selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?>
            </optgroup>
          </select>
        </div>
        <div>
          <label class="form-label-custom" for="f-cat">Category</label>
          <select class="form-select-custom" id="f-cat" name="category_id">
            <option value="0">No category</option>
            <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (string)$m['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="full">
          <label class="form-label-custom" for="f-desc">Description</label>
          <textarea class="form-control-custom" id="f-desc" name="description" rows="4"><?= h($m['description']) ?></textarea>
        </div>

        <div class="sg-section"><b>02</b><div><strong>Upload your files</strong><small>Choose the media file and optional cover artwork.</small></div></div>

        <div>
          <label class="form-label-custom" id="media-file-label" for="media_file">Music / Video File *</label>
          <div class="sg-drop">
            <i class="bi bi-file-earmark-music"></i>
            <input class="form-control-custom mt-2" type="file" id="media_file" name="media_file" accept="video/*,audio/*,.mp4,.m4v,.mov,.webm,.mp3,.wav,.ogg,.m4a" <?= $id ? '' : 'required' ?>>
            <small id="media-file-help">Music: MP3, WAV, OGG, M4A &middot; Video: MP4, WEBM, MOV &middot; Maximum 200 MB</small>
            <?php if ($id && $m['file_path']): ?><small>Current file: <?= h(basename($m['file_path'])) ?> (leave empty to keep it)</small><?php endif; ?>
          </div>
        </div>
        <div>
          <label class="form-label-custom" for="cover_file">Cover artwork</label>
          <div class="sg-drop">
            <i class="bi bi-image"></i>
            <input class="form-control-custom mt-2" type="file" id="cover_file" name="cover_file" accept=".jpg,.jpeg,.png,.webp">
            <small>JPG, PNG or WEBP &middot; Landscape artwork looks best</small>
            <img id="cover-preview" alt="Cover preview" src="<?= h($m['cover_image'] ?? '') ?>" style="<?= !empty($m['cover_image']) ? '' : 'display:none;' ?>width:100%;max-height:190px;object-fit:cover;border-radius:12px;margin-top:12px">
          </div>
        </div>

        <div class="full d-flex flex-wrap gap-2 align-items-center">
          <button class="btn-custom btn-custom-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $id ? 'Save changes' : 'Publish to library' ?></button>
          <a class="btn-custom btn-custom-secondary" href="media.php">Cancel</a>
        </div>
      </div>
    </form>

    <script>
    (function(){
      const type=document.getElementById('f-type'),file=document.getElementById('media_file'),label=document.getElementById('media-file-label'),help=document.getElementById('media-file-help'),preview=document.getElementById('cover-preview'),cover=document.getElementById('cover_file');
      function syncType(clear){const v=type.value==='video';label.textContent=v?'Video file (MP4) *':'Music file *';help.textContent='Music: MP3, WAV, OGG, M4A · Video: MP4, WEBM, MOV · Maximum 200 MB';if(clear&&file.value)file.value='';}
      const VID=['mp4','webm','mov','m4v'],AUD=['mp3','wav','ogg','m4a'];
      function readDur(f){const u=URL.createObjectURL(f),el=document.createElement(type.value==='video'?'video':'audio');el.preload='metadata';el.onloadedmetadata=function(){if(isFinite(el.duration))document.getElementById('duration').value=Math.round(el.duration);URL.revokeObjectURL(u);};el.src=u;}
      file.addEventListener('change',function(){const f=file.files[0];if(!f)return;const e=f.name.split('.').pop().toLowerCase();if(VID.indexOf(e)>-1&&type.value!=='video'){type.value='video';syncType(false);}else if(AUD.indexOf(e)>-1&&type.value!=='music'){type.value='music';syncType(false);}readDur(f);});
      type.addEventListener('change',function(){syncType(true);});
      syncType(false);
      cover.addEventListener('change',function(){const f=this.files&&this.files[0];if(!f){return;}if(!f.type.startsWith('image/'))return;preview.src=URL.createObjectURL(f);preview.style.display='block';});
    })();
    </script>
<?php require 'footer.php'; ?>
