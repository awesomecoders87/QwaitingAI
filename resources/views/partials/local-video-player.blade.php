<style>
  #videoContainer video {
    width: 100%;
    height: auto;
    display: block;
  }
  #uploadBtn, #clearBtn, #playlistBtn {
    position: absolute;
    z-index: 5;
    width: 40px;
    height: 40px;
    background: #fff;
    border-radius: 50%;
    top: 5px;
    border: none;
    cursor: pointer;
  }
  #uploadBtn { left: 5px; }
  #clearBtn { left: 55px; }
  #playlistBtn { left: 105px; }

  /* Playlist popup */
  #playlistPopup {
    position: absolute;
    top: 110px;
    left: 5px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    width: 250px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    display: none;
    z-index: 10;
  }
  #playlistPopup h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
  }
  #playlistPopup ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  #playlistPopup li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
    font-size: 14px;
  }
  #playlistPopup li button {
    background: none;
    border: none;
    cursor: pointer;
    color: red;
    font-size: 14px;
  }

  /* Toggle button */
  .toggle-btn {
    position: absolute;
    top: 10px;
    left: 0;
    z-index: 20;
    background: #a2a0a0b0;
    color: #fff;
    border: none;
    padding: 5px;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    line-height: 1;
    font-size: 14px;
  }

  /* Side menu container */
  .side-menu {
    position: absolute;
    top: 60px;
    left: -160px; /* hidden by default */
    width: 160px;
    height: 50px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #fff;
    padding: 10px 5px;
    border-radius: 0 8px 8px 0;
    box-shadow: 2px 0 6px rgba(0,0,0,0.2);
    transition: left 0.3s ease;
    z-index: 15;
  }

  .side-menu.show {
    left: 0; /* slide in */
  }

  .side-menu button {
    width: 40px;
    height: 40px;
    border: none;
    background: #f5f5f5;
    border-radius: 50%;
    cursor: pointer;
  }
  .video-outer,#videoContainer{
    height: 100%;
    min-height: 100%;
    min-width: 100%;
    object-fit:cover
  }
  #videoContainer{
    display: flex;
    justify-content: center;
    align-items: center;
  }
  #videoContainer video{
    width: auto;
    height: auto;
    max-height: 100%;
    max-width: 100%;
    min-height: 100%;
    min-width: 100%;
    object-fit: contain;
    background:#000
  }
  .no-video{
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 90vh;
  }
</style>

<button id="toggleMenuBtn" class="toggle-btn">
  ➤
</button>
<div class="video-outer">
  <input type="file" id="videoInput" accept="video/*" multiple style="display: none;" />

  <div id="sideMenu" class="side-menu">
    <button id="uploadBtn">
      <img src="https://cdn-icons-png.flaticon.com/512/1828/1828919.png" alt="Upload" width="30" />
    </button>
    <button id="clearBtn">
      <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" alt="Clear" width="30" />
    </button>
    <button id="playlistBtn">
      <img src="https://cdn-icons-png.flaticon.com/512/748/748113.png" alt="Playlist" width="30" />
    </button>
  </div>

  <div id="videoContainer"></div>

  <!-- Playlist Popup -->
  <div id="playlistPopup">
    <h4>Saved Videos</h4>
    <ul id="videoList"></ul>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
  let db;
  let videoFiles = []; // {id, file, url}
  let videoNames = [];
  let currentIndex = 0;
  let videoElement = null;

  const request = indexedDB.open("VideoDB", 1);

  request.onupgradeneeded = function (e) {
    db = e.target.result;
    if (!db.objectStoreNames.contains("videos")) {
      db.createObjectStore("videos", { keyPath: "id", autoIncrement: true });
    }
  };

  request.onsuccess = function (e) {
    db = e.target.result;
    loadVideosFromDB();
  };

  request.onerror = function (e) {
    console.error("IndexedDB error:", e.target.errorCode);
  };

  function saveVideosToDB(files) {
    const transaction = db.transaction("videos", "readwrite");
    const store = transaction.objectStore("videos");

    store.clear().onsuccess = function () {
      files.forEach(file => {
        store.add({ file, name: file.name });
      });
    };

    transaction.oncomplete = function () {
      loadVideosFromDB();
    };
  }

  function loadVideosFromDB() {
    // revoke old blob urls to prevent memory leaks
    videoFiles.forEach(v => URL.revokeObjectURL(v.url));

    videoFiles = [];
    videoNames = [];
    const transaction = db.transaction("videos", "readonly");
    const store = transaction.objectStore("videos");

    store.getAll().onsuccess = function (e) {
      const results = e.target.result;
      results.forEach(item => {
        const blobUrl = URL.createObjectURL(item.file); // persistent URL
        videoFiles.push({ id: item.id, file: item.file, url: blobUrl });
        videoNames.push({ id: item.id, name: item.name });
      });

      renderPlaylist();

      if (videoFiles.length > 0) {
        currentIndex = 0;
        playVideo(currentIndex);
      } else {
        $('#videoContainer').empty().append("<p class='no-video'>No Videos</p>");
      }
    };
  }

  function deleteVideoFromDB(id) {
    const transaction = db.transaction("videos", "readwrite");
    const store = transaction.objectStore("videos");
    store.delete(id).onsuccess = function () {
      console.log("Deleted video id:", id);
      loadVideosFromDB();
    };
  }

  function clearVideosFromDB() {
    const transaction = db.transaction("videos", "readwrite");
    const store = transaction.objectStore("videos");
    store.clear().onsuccess = function () {
      // revoke blob urls
      videoFiles.forEach(v => URL.revokeObjectURL(v.url));
      videoFiles = [];
      videoNames = [];
      currentIndex = 0;
      $('#videoContainer').empty().append("<p class='no-video'>No Videos</p>");
      $('#videoList').empty();
    };
  }

  function createVideo(src) {
    $('#videoContainer').empty();
    videoElement = $('<video>', {
      src: src,
      controls: true,
      autoplay: true,
      muted: true
    });
    videoElement.on('ended', function () {
      currentIndex = (currentIndex + 1) % videoFiles.length;
      playVideo(currentIndex);
    });
    $('#videoContainer').append(videoElement);
  }

  function playVideo(index) {
    if (!videoFiles[index]) return;
    createVideo(videoFiles[index].url); // use persistent URL
  }

  function renderPlaylist() {
    $('#videoList').empty();
    videoNames.forEach(item => {
      const li = $("<li>").text(item.name);
      const delBtn = $("<button>🗑</button>").on("click", function () {
        if (confirm("Delete this video?")) {
          deleteVideoFromDB(item.id);
        }
      });
      li.append(delBtn);
      $('#videoList').append(li);
    });
  }

  // Buttons
  $('#uploadBtn').on('click', function () { $('#videoInput').click(); });
  $('#clearBtn').on('click', function () {
    if (confirm("Delete all videos?")) clearVideosFromDB();
  });
  $('#playlistBtn').on('click', function () {
    $('#playlistPopup').toggle();
  });

  $('#videoInput').on('change', function (e) {
    const files = Array.from(e.target.files);
    if (files.length > 20) {
      alert("You can upload up to 20 videos only.");
      return;
    }
    saveVideosToDB(files);
    $(this).val("");
  });
});

// toggle side menu
$('#toggleMenuBtn').on('click', function () {
  $('#sideMenu').toggleClass('show');
  $(this).text($('#sideMenu').hasClass('show') ? "◀" : "➤");
});
</script>
