
function mediaSearch(){
  const query = $("#addmedia").val().trim();
  getOzzyID(query);
}

function getOzzyID(trackQuery){
  $.ajax({
    async: true,
    crossDomain: true,
    url: 'https://spotify23.p.rapidapi.com/search/' +
         '?q=' + encodeURIComponent('Ozzy Osbourne') +
         '&type=artists' +
         '&limit=1',
    method: 'GET',
    headers: {
      'x-rapidapi-key':   '938b59c587mshd450facca2ef9f8p1736dejsn1f2e8e758cfd',
      'x-rapidapi-host':  'spotify23.p.rapidapi.com'
    }
  }).done(response => {
    const items = response.artists?.items || [];
    if (!items.length) {
      console.error("No artist found");
      $('#ozzymedia').text("No artist found.");
      return;
    }
    const first = items[0];
    const uri = first.uri 
             || first.data?.uri 
             || first.data?.resourceUri 
             || first.resource_uri 
             || null;

    if (!uri) {
      console.error("Cannot find URI in:", first);
      $('#ozzymedia').text("Malformed artist response.");
      return;
    }
    const ozzyId = uri.split(':').pop();
    console.log(ozzyId);
    getOzzyMedia(ozzyId, trackQuery);
  }).fail(err => {
    console.error("Artist search failed:", err);
    $('#ozzymedia').text("Failed to find artist.");
  });
}

function getOzzyMedia(artistId, trackQuery) {
  $.ajax({
    url: `https://spotify23.p.rapidapi.com/artist_albums/?id=${artistId}&limit=50`,
    method: 'GET',
    headers: {
      'x-rapidapi-key':  '938b59c587mshd450facca2ef9f8p1736dejsn1f2e8e758cfd',
      'x-rapidapi-host': 'spotify23.p.rapidapi.com'
    }
  })
  .done(albumsResp => {
    $('#ozzymedia').empty();
    console.log(albumsResp);
    const albumArray = albumsResp.data.artist.discography.albums.items;
    const albumIds   = albumArray.map(a => a.id);
    console.log(albumArray);
    index = 0;
    const albums = albumArray.map(album => ({
      name: album.releases.items[0].name,
      shareUrl:  album.releases.items[0].sharingInfo.shareUrl
    }));
    console.log(albums);
    if(trackQuery){
      const searchresult = albums.find(a =>a.name.includes(trackQuery));
      $('#ozzymedia').append(
        $('<div>', {class:'album'}).append(
          $('<h4>').text(searchresult.name),
          $('<a>', { href: searchresult.shareUrl, text: "Link to Media", target: '_blank'}).addClass('media-link')
        )
      );
    }
    else{
      albums.forEach(a => {
        $('#ozzymedia').append(
          $('<div>', {class:'album'}).append(
            $('<h4>').text(a.name),
            $('<a>', { href: a.shareUrl, text: "Link to Media", target:'_blank'})
          )
        );
      });
    }
    })
      .catch(err => {
        console.error('Error fetching tracks:', err);
        $('#ozzymedia').text('Failed to load tracks.');
      });
    }
