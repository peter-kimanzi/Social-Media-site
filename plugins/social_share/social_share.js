function share_social(social, url, id) {
	// Get the message content
	var content = $.trim($("#message_text"+id).text());
	
	// Get the message author's image
	var image = encodeURIComponent($("#message"+id).find('img').attr('src'));

	if (social !== 'gmail' && social !== 'yahoo' && social !== 'email') {
		content = content.substr(0, 350);
	}

	if(social == 'facebook') {
		window.open("https://www.facebook.com/sharer/sharer.php?u="+url, "", "width=500, height=250");
	} else if(social == 'twitter') {
		window.open("https://twitter.com/intent/tweet?text="+encodeURIComponent(content)+"&url="+url, "", "width=500, height=250");
	} else if(social == 'pinterest') {
		window.open("https://pinterest.com/pin/create/button/?url="+url+"&description="+encodeURIComponent(content)+"&media="+image, "", "width=500, height=250");
	} else if(social == 'tumblr') {
		window.open("https://www.tumblr.com/widgets/share/tool?canonicalUrl="+url, "", "width=500, height=250");
	} else if(social == 'email') {
		window.open("mailto:?body="+encodeURIComponent(content)+" - "+url, "_self");
	} else if(social == 'vkontakte') {
		window.open("http://vkontakte.ru/share.php?url="+url+"&description="+encodeURIComponent(content)+"&image="+image+"&noparse=true", "", "width=500, height=500");
	} else if(social == 'reddit') {
		window.open("https://www.reddit.com/submit?url="+url, "", "width=850, height=500");
	} else if(social == 'linkedin') {
		window.open("https://www.linkedin.com/cws/share?url="+url, "", "width=500, height=350");
	} else if(social == 'whatsapp') {
		window.open("whatsapp://send?text="+encodeURIComponent(content)+" - "+url, "", "width=500, height=350");
	} else if(social == 'viber') {
		window.open("viber://forward?text="+encodeURIComponent(content)+" - "+url, "", "width=500, height=350");
	} else if(social == 'digg') {
		window.open("http://digg.com/submit?phase=&url="+url, "", "width=500, height=350");
	} else if(social == 'evernote') {
		window.open("https://www.evernote.com/clip.action?url="+url, "", "width=850, height=450");
	} else if(social == 'yummly') {
		window.open("http://www.yummly.com/urb/verify?url="+url+"&title="+encodeURIComponent(content)+"&image=&yumtype=button", "", "width=850, height=450");
	} else if(social == 'yahoo') {
		window.open("http://compose.mail.yahoo.com/?body="+encodeURIComponent(content)+" - "+url, "", "width=850, height=450");
	} else if(social == 'gmail') {
		window.open("https://mail.google.com/mail/u/0/?view=cm&fs=1&su=&body="+encodeURIComponent(content)+" - "+url+"&ui=2&tf=1", "", "width=650, height=450");
	}
}