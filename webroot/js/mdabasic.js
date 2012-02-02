var MDA={};

MDA.Days = {
	dayStr:['日','月','火','水','木','金','土'],
	num2str:function(n, i){
		n = n + '';
		while(n.length < i) {n = '0' + n;}
		return n;
	},
	time4Disp:function(T){
		return '<span class="bold">' + parseInt(T.getMonth() + 1) + '</span><span class="small">月</span>' +
			'<span class="bold">' + T.getDate() + '</span><span class="small">日</span>' +
			'(<span class="bold">' + this.dayStr[T.getDay()] + '</span>) ' + this.time4DispH(T);
	},
	time4DispH:function(T){
		return '<span class="bold">' + this.num2str(T.getHours(),2) + ':' + this.num2str(T.getMinutes(),2) + '</span>'
	}
}

MDA.Cookie={
	CookieName:'Ck_',
	DefaultLifeTime:365,	// day
	ini:function(){
		if(INIset.CookieName){this.CookieName=INIset.CookieName;}
	},
	set:function(N,V,D,E){
		if((N!=null)&&(V!=null)) {
			N=this.CookieName+N;
			if (!D){D=this.DefaultLifeTime;}
			D=parseInt(D);
			var sD=new Date();
			sD.setTime(sD.getTime()+(D*86400000));
			eD=sD.toGMTString();
			document.cookie=N+"="+(E?V:escape(V))+";expires="+eD;
			return true;
		}
		return false;
	},
	del:function(N){
		document.cookie=this.CookieName+N+"=;expires=thu,01-Jan-70 00:00:01 GMT";
		return true;
	},
	get:function(N,E){
		N=this.CookieName+N+'=';
		var C = document.cookie + ";";
		var s = C.indexOf(N);
		if(s != -1) {
			var e = C.indexOf(";", s);
			return E?C.substring(s + N.length, e):unescape(C.substring(s + N.length, e));
		}
		return false;
	}
};
