console.log("loaded");
var w;
var height;
var tmp;
var width;
var batch=0;
function dispatchRecentChanges($new=0){
	//fill the screen with columns of images
	w=window.innerWidth;

	//make it responsive
	if(w>1900){
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
	tmp=0;
	width=0;
	$('.mw-special-ArchiRecentChanges .latest-changes-recent-change-container').each(function(){
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

async function getArbreCategories($title){
	if ($title.startsWith("Personne:")) {
		return "wrong";
	}
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
		
		if (categorie[i]['title'].endsWith('(Structure)') || categorie[i]['title'].endsWith('(Courant_architectural)') || categorie[i]['title'].endsWith('(Type_d\'événement)')) {
			continue; //skip les catégories qu'on ne veut pas (A ajouté si d'autres catégories automatique existent)
		}
		else{
			tmp=await getArbreCategories("Catégorie:"+categorie[i]['title']);
			if(tmp!="wrong"){
				return tmp.concat([categorie[i]['title']]);
			}
		}
	}
	return "wrong";
}

async function addRecentChanges($rccontinue){
	$("#voir-plus").hide();
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
			var start=new Date().getTime();
			var recentchanges= response.query.recentchanges;
			recentchanges.sort(function(a, b){if(new Date(a.timestamp) > new Date(b.timestamp)){return -1;}if(new Date(a.timestamp) < new Date(b.timestamp)){return 1;}return 0;});
			for (var rc in recentchanges) {
				if(recentchanges[rc].title && recentchanges[rc].title!='Adresse: Bac à sable'){
					var $recentChangeContainer = $('<article class="latest-changes-recent-change-container" id="'+batch+'"></article>');
					$recentChangeContainer.css({
						top: '100%',
						left: '100%',
					});
					batch++;
					var $recentChange = $('<article class="latest-changes-recent-change"></article>');
					var $h3 = $('<h3><span id="'+recentchanges[rc].title+'" class="mw-headline"></span></h3>').text(recentchanges[rc].title.replace(/(Adresse:|Personne:)/g, '').replace(/\(.*\)/, '')); //remove the namespace and the city name
					$recentChange.append($h3);

					var title=new mw.Title(recentchanges[rc].title);
					var res=await Promise.allSettled([
						api.get({action: "ask", query: "[["+recentchanges[rc].title+"]]|?Image principale|?Adresse complète", format: "json"}),
						getArbreCategories(recentchanges[rc].title),
						api.parse(title,{section:1})
					]).catch(function(error){console.log(error);}); //await simultanéé pour économiser du temps

					var prop=res[0].value.query.results[recentchanges[rc].title];

					var catégories=res[1].value;

					var htmlText=res[2].value;
					var text=$('<p></p>');
					$(htmlText).find('p').each(function(){
						text.append($(this).html());
					});
					$(text).find('sup').remove();
					$(text).find('a').contents().unwrap();
					htmlText=$(text).text();
					if(htmlText.length>120){
						var trimmedText=htmlText.substring(0,120);
						trimmedText = trimmedText.substring(0, Math.min(trimmedText.length, trimmedText.lastIndexOf(" "))); //dosen't cut a word
						htmlText=trimmedText+"...";
					}


					if(prop["printouts"]["Adresse complète"].length>0){
						$recentChange.append($('<p></p>').text(prop["printouts"]["Adresse complète"][0]['fulltext']));
					}
					if(prop["printouts"]["Image principale"].length==0){
						var $image="Fichier:Image-manquante.jpg"
					} else {
						var $image=prop["printouts"]["Image principale"][0]['fulltext'];
					}

					if(catégories!="wrong"){
						var stringCatégories='<a href="/catégorie:'+catégories[0]+'" title="Catégorie:'+catégories[0]+'">'+catégories[0]+'</a>';
						if(catégories[1]){
							stringCatégories+=' > <a href="/catégorie:'+catégories[1]+'" title="Catégorie:'+catégories[1]+'">'+catégories[1]+'</a>';
							/*if(catégories[2] && !catégories[2].startsWith("Autre")){
								stringCatégories+=' > <a href="/catégorie:'+catégories[2]+'" title="Catégorie:'+catégories[2]+'">'+catégories[2]+'</a>';

							}*/
						}
						$recentChange.append(stringCatégories)
					}

					await api.parse('[['+$image+'|thumb|left|100px]]').done(function(data){
						$recentChange.append($(data).find('div').first().html());
					});


					var date=new Date(recentchanges[rc].timestamp);

					date=$('<p></p>').append($('<i></i>').text(date.getDay()+'/'+date.getMonth()+'/'+date.getFullYear()));
					$recentChange.append(date);

					$recentChange.append(htmlText);

					$recentChange.append($('<p></p>').append($('<a></a>').attr('href', '/'+recentchanges[rc].title).attr('title',recentchanges[rc].title).text(mw.message('readthis').text())));
					$recentChangeContainer.hide();
					$recentChangeContainer.append($recentChange);
					$(".latest-block").append($recentChangeContainer);
					displayImage($recentChangeContainer);
					
				}
			}
			$("#voir-plus").data('val', response.continue.rccontinue);
			$("#voir-plus").show();
			console.log("temps de chargement: "+(new Date().getTime()-start));
		}).catch(function(error){console.log(error);});
	
	
}

function displayImage($elt){
	var $headerImage = $elt.find('a.image>img').first();
	
	var $headerImageUrl=$headerImage.attr('src');
	if(typeof($headerImageUrl)!='undefined'){
		var $ImageUrl;
		if($headerImageUrl.substr(0, $headerImageUrl.lastIndexOf('thumb') >= 0)){
			$headerImageUrl=$headerImageUrl.substr(0, $headerImageUrl.lastIndexOf('/'));
			$ImageUrl=$headerImageUrl.replace(/\/thumb/,'');
		}
		else{
			$ImageUrl=$headerImageUrl;
		}
		var url = $elt.find('p > a').attr('href');

		var img=new Image();
		img.src=$ImageUrl;
		img.classList.add('header-image');
		img.style.width='100%';
		img.style.height='100%';
		img.onload=function(){orderOne($elt);};
		var $imageA=$('<a></a>').attr('href', url).append(img);
		$elt.prepend($imageA);
		$headerImage.parents('.thumb').hide();
		$headerImage.parents('.thumbinner').hide();
	}
	
}
function orderOne($elt){
	$elt.fadeIn(1000);
	$elt.css({
		"transition-property": 'top',
		"transition-duration": '1s',
		"transition-timing-function": 'cubic-bezier(0,-0.01,.12,1.1)',
		left: width + 'px',
		top: height[tmp] + 'px',
		width: height.length==1 ? '100%' : (100/height.length) + '%'
	});
	height[tmp]+=$elt.outerHeight();
	width+=$elt.outerWidth();
	tmp++;
	if(tmp==height.length){
		tmp=0;
		width=0;
	}
	$(".mw-special-ArchiRecentChanges #content").css({
		height: (Math.max.apply(null,height) +100)+ 'px'
	});
	
	//$elt.show();
}
function orderAll(){
	w=window.innerWidth;
	//make it responsive
	if(w>1900){
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
	tmp=0;
	width=0;
	$('.mw-special-ArchiRecentChanges .latest-changes-recent-change-container').each(function(){
		displayImage($(this));
		$(this).id=batch;
		batch++;

	});
	
}
console.log("ArchiRecentChanges");
$(document).ready(function(){
	
	$('.mw-special-ArchiRecentChanges .latest-changes-recent-change-container').each(function(){
		$(this).hide();
		$(this).css({
			top: '100%'
		});
	});
	orderAll();

    window.addEventListener('resize', function(){
        dispatchRecentChanges();
    },true);
	
	$("#voir-plus").click(function(){
		addRecentChanges($(this).data('val'));
	});
});