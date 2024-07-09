var batch=0;
function dispatchRecentChanges($new=0){
	//fill the screen with columns of images
	var w=window.innerWidth;
	let height;
	//make it responsive
	if(w==1920/4){
		height=[100,100,100,100,100];
	}else if(w>((1920/4)*3)){
		height=[100,100,100,100];
	} else if(w>((1920/4)*2)){
		height=[100,100,100];
	} else if(w>((1920/4)*1)){
		height=[100,100];
	} else {
		height=[100];
	}
	var tmp=0;
	var width=0;
	$('.mw-special-ArchiRecentChanges .latest-changes-recent-change-container').each(function(){
		
		this.id=batch;
		batch++;
		$(this).css({
			left: width + 'px',
			top: height[tmp] + 'px',
			width: height.length==1 ? '100%' : (100/height.length) + '%'
		});
		height[tmp]+=$(this).outerHeight();
		width+=$(this).outerWidth();
		tmp++;
		if(tmp==height.length){
			tmp=0;
			width=0;
		}
	});
	//set the height of the screen
	$(".mw-special-ArchiRecentChanges #content").css({
		height: (Math.max.apply(null,height) +100)+ 'px'
	});
}
function displayImages(){
	$('.mw-special-ArchiRecentChanges .latest-changes-recent-change').each(function(){
		
        var $headerImage = $(this).find('a.image>img').first();
		console.log($headerImage.parents('.thumb').is(':hidden'));
		if (!($headerImage.parents('.thumb').is(':hidden'))) {
        	var $headerImageUrl=$headerImage.attr('src');
        	if(typeof($headerImageUrl)!='undefined'){
        	    let $ImageUrl;
        	    if($headerImageUrl.substr(0, $headerImageUrl.lastIndexOf('thumb') >= 0)){
        	        $headerImageUrl=$headerImageUrl.substr(0, $headerImageUrl.lastIndexOf('/'));
        	        $ImageUrl=$headerImageUrl.replace(/\/thumb/,'');
        	    }
        	    else{
        	        $ImageUrl=$headerImageUrl;
        	    }
        	    var url = $(this).find('p > a').attr('href');
        	    $(this).prepend('<a href="' + url + '"><img src="'+$ImageUrl+'" class="header-image" style="width:100%;height:100%;"></a>');
        	    $headerImage.parents('.thumb').hide();

        	}
		}
    });
}

async function getArbreCategories($title){
	const api= new mw.Api();
	var categorie=await api.getCategories($title).done(function(data){
		return data;
	});
	if(categorie.length>0){}
	else {
		return "wrong";
	}
	
	if(categorie[0]['title']=='Pays'){
		return [];
	}
	var tmp=[];
	for(var i in categorie){
		tmp=await getArbreCategories("Catégorie:"+categorie[i]['title']);
		if(tmp!="wrong"){
			return tmp.push(categorie[i]['title']);
		}
	}
	return "wrong";
}

async function addRecentChanges($rccontinue){
	var url = "https://www.archi-wiki.org/api.php";
	var params ={
		action: "query",
		list: "recentchanges",
		rcnamespace: "4000|4006",
		rctoponly: true,
		rcshow: '!redirect',
		rclimit: 20,
		rccontinue: $rccontinue,
		format: "json"
	};

	const api= new mw.Api();
	await api.get(params)
		.then(async function(response) {
			var recentchanges= response.query.recentchanges;
			recentchanges.sort(function(a, b){if(new Date(a.timestamp) > new Date(b.timestamp)){return -1;}if(new Date(a.timestamp) < new Date(b.timestamp)){return 1;}return 0;});
			for (var rc in recentchanges) {
				if(recentchanges[rc].title && recentchanges[rc].title!='Adresse: Bac à sable'){
					var $recentChangeContainer = $('<article class="latest-changes-recent-change-container" id="'+batch+'"></article>');
					batch++;
					var $recentChange = $('<article class="latest-changes-recent-change"></article>');
					var $h3 = $('<h3><span id="'+recentchanges[rc].title+'" class="mw-headline"></span></h3>').text(recentchanges[rc].title.replace(/(Adresse:|Personne:)/g, '').replace(/\(.*\)/, '')); //remove the namespace and the city name
					$recentChange.append($h3);
					

					/*var categoryTree=[];
					await api.getCategories(recentchanges[rc].title).done(async function(data){
						for(var cat in data){
							await api.getCategories("Catégorie:"+data[cat]['title']).done(async function(data2){
								if(data2.length>0){

									await api.getCategories("Catégorie:"+data2[0]['title']).done(async function(data3){
										if(data3.length>0){
											await api.getCategories("Catégorie:"+data3[0]['title']).done(async function(data4){
												if(data4.length>0){
													categoryTree.push(data[cat]['title']);
													categoryTree.push(data2[0]['title']);
													categoryTree.push(data3[0]['title']);
													categoryTree.push(data4[0]['title']);
													
												}
											});
										}
									});
								}
							});
						}
					});
					console.log(categoryTree);
					*/
					console.log("145: "+await getArbreCategories(recentchanges[rc].title));
					var prop=await api.get({action: "ask", query: "[["+recentchanges[rc].title+"]]|?Image principale|?Adresse complète", format: "json"})
						.then(function(response) {
							var results = response.query.results;
							return results[recentchanges[rc].title];
						}).catch(function(error){console.log(error);});

					if(prop["printouts"]["Adresse complète"].length>0){
						$recentChange.append($('<p></p>').text(prop["printouts"]["Adresse complète"][0]['fulltext']));
					}
					if(prop["printouts"]["Image principale"].length==0){
						var $image="Fichier:Image-manquante.jpg"
					} else {
						var $image=prop["printouts"]["Image principale"][0]['fulltext'];
					}


					await api.parse('[['+$image+'|thumb|left|100px]]').done(function(data){
						$recentChange.append($(data).find('div').first().html());
					});

					
					/*$.ajax({
						url: "extensions/ArchiRecentChanges/addWikiTextAsContent.php",
						type: "POST",
						dataType: "json",
						data: {title: '[['+$image+'|thumb|left|100px]]'},
						success: function(data, textstatus){
							console.log(data);
							console.log(data['result']);
							$recentChange.append(data['result']);
						},
						error: function(xhr, status, error){
							//var err=JSON.parse();
						}
					});*/

					$recentChangeContainer.append($recentChange);
					$(".latest-block").append($recentChangeContainer);
				}
			}
		}).catch(function(error){console.log(error);});
	dispatchRecentChanges();
	
}
$(document).ready(function(){

    //Put the image in the right place in ArchiRecentChanges
    //displayImages();
    setTimeout(() => {
        dispatchRecentChanges();
    }, 500);
    window.addEventListener('resize', function(){
        dispatchRecentChanges();
    },true);

	$("#voir-plus").click(function(){
		addRecentChanges($(this).data('val'));
		//displayImages();
	});
});