(function(J,le){typeof exports=="object"&&typeof module<"u"?module.exports=le():typeof define=="function"&&define.amd?define(le):(J=typeof globalThis<"u"?globalThis:J||self,J.ChatPilotWidget=le())})(this,(function(){"use strict";const J="chatpilot_session";function le(N,H){const v=N.replace(/\/$/,"");function I(){try{const y=localStorage.getItem(J);return y?JSON.parse(y):null}catch{return null}}function C(y){localStorage.setItem(J,JSON.stringify(y))}function x(){localStorage.removeItem(J)}async function c(y,p,P=null){const f=I(),_={"Content-Type":"application/json",Accept:"application/json","X-Site-Key":H};f!=null&&f.visitorToken&&(_["X-Visitor-Token"]=f.visitorToken);let d;try{const g={method:y,headers:_,body:P?JSON.stringify(P):null};y==="GET"&&p==="/v1/site/config"&&(g.cache="no-store"),d=await fetch(`${v}/api${p}`,g)}catch{const g=new Error("SERVICE_UNAVAILABLE");throw g.code="SERVICE_UNAVAILABLE",g}if(d.status===429){const g=await d.json().catch(()=>({})),S=new Error("Rate limited");throw S.retryAfter=g.retry_after||3,S.code="RATE_LIMITED",S}if(d.status===401){x();const g=await d.json().catch(()=>({})),S=new Error(g.message||"Unauthorized");throw S.status=401,S.code="UNAUTHORIZED",S}if(!d.ok){const g=await d.json().catch(()=>({})),S=new Error(g.message||`HTTP ${d.status}`);throw S.status=d.status,S}return d.json()}return{getSession:I,saveSession:C,clearSession:x,async getConfig(){return c("GET","/v1/site/config")},async createConversation(y,p={}){var _,d;const P=await c("POST","/v1/conversations",{visitor_name:y,metadata:p}),f={conversationId:((_=P.data)==null?void 0:_.id)||P.id,visitorToken:((d=P.data)==null?void 0:d.visitor_token)||P.visitor_token,visitorName:y};return C(f),f},async getMessages(y,p=null,P=null){const f=new URLSearchParams;p&&f.set("after",p),P&&f.set("language",P);const _=f.toString()?`?${f.toString()}`:"";return c("GET",`/v1/conversations/${y}/messages${_}`)},async sendMessage(y,p,P=null){const f={text:p};return P&&(f.language=P),c("POST",`/v1/conversations/${y}/messages`,f)}}}function Te(N){return N&&N.__esModule&&Object.prototype.hasOwnProperty.call(N,"default")?N.default:N}var ge={exports:{}};/*!
 * Pusher JavaScript Library v8.4.0
 * https://pusher.com/
 *
 * Copyright 2020, Pusher
 * Released under the MIT licence.
 */var tt;function Ct(){return tt||(tt=1,(function(N,H){(function(I,C){N.exports=C()})(window,function(){return(function(v){var I={};function C(x){if(I[x])return I[x].exports;var c=I[x]={i:x,l:!1,exports:{}};return v[x].call(c.exports,c,c.exports,C),c.l=!0,c.exports}return C.m=v,C.c=I,C.d=function(x,c,y){C.o(x,c)||Object.defineProperty(x,c,{enumerable:!0,get:y})},C.r=function(x){typeof Symbol<"u"&&Symbol.toStringTag&&Object.defineProperty(x,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(x,"__esModule",{value:!0})},C.t=function(x,c){if(c&1&&(x=C(x)),c&8||c&4&&typeof x=="object"&&x&&x.__esModule)return x;var y=Object.create(null);if(C.r(y),Object.defineProperty(y,"default",{enumerable:!0,value:x}),c&2&&typeof x!="string")for(var p in x)C.d(y,p,(function(P){return x[P]}).bind(null,p));return y},C.n=function(x){var c=x&&x.__esModule?function(){return x.default}:function(){return x};return C.d(c,"a",c),c},C.o=function(x,c){return Object.prototype.hasOwnProperty.call(x,c)},C.p="",C(C.s=2)})([(function(v,I,C){var x=this&&this.__extends||(function(){var w=function(u,h){return w=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(k,T){k.__proto__=T}||function(k,T){for(var m in T)T.hasOwnProperty(m)&&(k[m]=T[m])},w(u,h)};return function(u,h){w(u,h);function k(){this.constructor=u}u.prototype=h===null?Object.create(h):(k.prototype=h.prototype,new k)}})();Object.defineProperty(I,"__esModule",{value:!0});var c=256,y=(function(){function w(u){u===void 0&&(u="="),this._paddingCharacter=u}return w.prototype.encodedLength=function(u){return this._paddingCharacter?(u+2)/3*4|0:(u*8+5)/6|0},w.prototype.encode=function(u){for(var h="",k=0;k<u.length-2;k+=3){var T=u[k]<<16|u[k+1]<<8|u[k+2];h+=this._encodeByte(T>>>18&63),h+=this._encodeByte(T>>>12&63),h+=this._encodeByte(T>>>6&63),h+=this._encodeByte(T>>>0&63)}var m=u.length-k;if(m>0){var T=u[k]<<16|(m===2?u[k+1]<<8:0);h+=this._encodeByte(T>>>18&63),h+=this._encodeByte(T>>>12&63),m===2?h+=this._encodeByte(T>>>6&63):h+=this._paddingCharacter||"",h+=this._paddingCharacter||""}return h},w.prototype.maxDecodedLength=function(u){return this._paddingCharacter?u/4*3|0:(u*6+7)/8|0},w.prototype.decodedLength=function(u){return this.maxDecodedLength(u.length-this._getPaddingLength(u))},w.prototype.decode=function(u){if(u.length===0)return new Uint8Array(0);for(var h=this._getPaddingLength(u),k=u.length-h,T=new Uint8Array(this.maxDecodedLength(k)),m=0,M=0,X=0,ee=0,V=0,Z=0,G=0;M<k-4;M+=4)ee=this._decodeChar(u.charCodeAt(M+0)),V=this._decodeChar(u.charCodeAt(M+1)),Z=this._decodeChar(u.charCodeAt(M+2)),G=this._decodeChar(u.charCodeAt(M+3)),T[m++]=ee<<2|V>>>4,T[m++]=V<<4|Z>>>2,T[m++]=Z<<6|G,X|=ee&c,X|=V&c,X|=Z&c,X|=G&c;if(M<k-1&&(ee=this._decodeChar(u.charCodeAt(M)),V=this._decodeChar(u.charCodeAt(M+1)),T[m++]=ee<<2|V>>>4,X|=ee&c,X|=V&c),M<k-2&&(Z=this._decodeChar(u.charCodeAt(M+2)),T[m++]=V<<4|Z>>>2,X|=Z&c),M<k-3&&(G=this._decodeChar(u.charCodeAt(M+3)),T[m++]=Z<<6|G,X|=G&c),X!==0)throw new Error("Base64Coder: incorrect characters for decoding");return T},w.prototype._encodeByte=function(u){var h=u;return h+=65,h+=25-u>>>8&6,h+=51-u>>>8&-75,h+=61-u>>>8&-15,h+=62-u>>>8&3,String.fromCharCode(h)},w.prototype._decodeChar=function(u){var h=c;return h+=(42-u&u-44)>>>8&-c+u-43+62,h+=(46-u&u-48)>>>8&-c+u-47+63,h+=(47-u&u-58)>>>8&-c+u-48+52,h+=(64-u&u-91)>>>8&-c+u-65+0,h+=(96-u&u-123)>>>8&-c+u-97+26,h},w.prototype._getPaddingLength=function(u){var h=0;if(this._paddingCharacter){for(var k=u.length-1;k>=0&&u[k]===this._paddingCharacter;k--)h++;if(u.length<4||h>2)throw new Error("Base64Coder: incorrect padding")}return h},w})();I.Coder=y;var p=new y;function P(w){return p.encode(w)}I.encode=P;function f(w){return p.decode(w)}I.decode=f;var _=(function(w){x(u,w);function u(){return w!==null&&w.apply(this,arguments)||this}return u.prototype._encodeByte=function(h){var k=h;return k+=65,k+=25-h>>>8&6,k+=51-h>>>8&-75,k+=61-h>>>8&-13,k+=62-h>>>8&49,String.fromCharCode(k)},u.prototype._decodeChar=function(h){var k=c;return k+=(44-h&h-46)>>>8&-c+h-45+62,k+=(94-h&h-96)>>>8&-c+h-95+63,k+=(47-h&h-58)>>>8&-c+h-48+52,k+=(64-h&h-91)>>>8&-c+h-65+0,k+=(96-h&h-123)>>>8&-c+h-97+26,k},u})(y);I.URLSafeCoder=_;var d=new _;function g(w){return d.encode(w)}I.encodeURLSafe=g;function S(w){return d.decode(w)}I.decodeURLSafe=S,I.encodedLength=function(w){return p.encodedLength(w)},I.maxDecodedLength=function(w){return p.maxDecodedLength(w)},I.decodedLength=function(w){return p.decodedLength(w)}}),(function(v,I,C){Object.defineProperty(I,"__esModule",{value:!0});var x="utf8: invalid string",c="utf8: invalid source encoding";function y(f){for(var _=new Uint8Array(p(f)),d=0,g=0;g<f.length;g++){var S=f.charCodeAt(g);S<128?_[d++]=S:S<2048?(_[d++]=192|S>>6,_[d++]=128|S&63):S<55296?(_[d++]=224|S>>12,_[d++]=128|S>>6&63,_[d++]=128|S&63):(g++,S=(S&1023)<<10,S|=f.charCodeAt(g)&1023,S+=65536,_[d++]=240|S>>18,_[d++]=128|S>>12&63,_[d++]=128|S>>6&63,_[d++]=128|S&63)}return _}I.encode=y;function p(f){for(var _=0,d=0;d<f.length;d++){var g=f.charCodeAt(d);if(g<128)_+=1;else if(g<2048)_+=2;else if(g<55296)_+=3;else if(g<=57343){if(d>=f.length-1)throw new Error(x);d++,_+=4}else throw new Error(x)}return _}I.encodedLength=p;function P(f){for(var _=[],d=0;d<f.length;d++){var g=f[d];if(g&128){var S=void 0;if(g<224){if(d>=f.length)throw new Error(c);var w=f[++d];if((w&192)!==128)throw new Error(c);g=(g&31)<<6|w&63,S=128}else if(g<240){if(d>=f.length-1)throw new Error(c);var w=f[++d],u=f[++d];if((w&192)!==128||(u&192)!==128)throw new Error(c);g=(g&15)<<12|(w&63)<<6|u&63,S=2048}else if(g<248){if(d>=f.length-2)throw new Error(c);var w=f[++d],u=f[++d],h=f[++d];if((w&192)!==128||(u&192)!==128||(h&192)!==128)throw new Error(c);g=(g&15)<<18|(w&63)<<12|(u&63)<<6|h&63,S=65536}else throw new Error(c);if(g<S||g>=55296&&g<=57343)throw new Error(c);if(g>=65536){if(g>1114111)throw new Error(c);g-=65536,_.push(String.fromCharCode(55296|g>>10)),g=56320|g&1023}}_.push(String.fromCharCode(g))}return _.join("")}I.decode=P}),(function(v,I,C){v.exports=C(3).default}),(function(v,I,C){C.r(I);class x{constructor(e,t){this.lastId=0,this.prefix=e,this.name=t}create(e){this.lastId++;var t=this.lastId,r=this.prefix+t,i=this.name+"["+t+"]",s=!1,l=function(){s||(e.apply(null,arguments),s=!0)};return this[t]=l,{number:t,id:r,name:i,callback:l}}remove(e){delete this[e.number]}}var c=new x("_pusher_script_","Pusher.ScriptReceivers"),y={VERSION:"8.4.0",PROTOCOL:7,wsPort:80,wssPort:443,wsPath:"",httpHost:"sockjs.pusher.com",httpPort:80,httpsPort:443,httpPath:"/pusher",stats_host:"stats.pusher.com",authEndpoint:"/pusher/auth",authTransport:"ajax",activityTimeout:12e4,pongTimeout:3e4,unavailableTimeout:1e4,userAuthentication:{endpoint:"/pusher/user-auth",transport:"ajax"},channelAuthorization:{endpoint:"/pusher/auth",transport:"ajax"},cdn_http:"http://js.pusher.com",cdn_https:"https://js.pusher.com",dependency_suffix:""},p=y;class P{constructor(e){this.options=e,this.receivers=e.receivers||c,this.loading={}}load(e,t,r){var i=this;if(i.loading[e]&&i.loading[e].length>0)i.loading[e].push(r);else{i.loading[e]=[r];var s=A.createScriptRequest(i.getPath(e,t)),l=i.receivers.create(function(b){if(i.receivers.remove(l),i.loading[e]){var L=i.loading[e];delete i.loading[e];for(var O=function(B){B||s.cleanup()},R=0;R<L.length;R++)L[R](b,O)}});s.send(l)}}getRoot(e){var t,r=A.getDocument().location.protocol;return e&&e.useTLS||r==="https:"?t=this.options.cdn_https:t=this.options.cdn_http,t.replace(/\/*$/,"")+"/"+this.options.version}getPath(e,t){return this.getRoot(t)+"/"+e+this.options.suffix+".js"}}var f=new x("_pusher_dependencies","Pusher.DependenciesReceivers"),_=new P({cdn_http:p.cdn_http,cdn_https:p.cdn_https,version:p.VERSION,suffix:p.dependency_suffix,receivers:f});const d={baseUrl:"https://pusher.com",urls:{authenticationEndpoint:{path:"/docs/channels/server_api/authenticating_users"},authorizationEndpoint:{path:"/docs/channels/server_api/authorizing-users/"},javascriptQuickStart:{path:"/docs/javascript_quick_start"},triggeringClientEvents:{path:"/docs/client_api_guide/client_events#trigger-events"},encryptedChannelSupport:{fullUrl:"https://github.com/pusher/pusher-js/tree/cc491015371a4bde5743d1c87a0fbac0feb53195#encrypted-channel-support"}}};var S={buildLogSuffix:function(n){const e="See:",t=d.urls[n];if(!t)return"";let r;return t.fullUrl?r=t.fullUrl:t.path&&(r=d.baseUrl+t.path),r?`${e} ${r}`:""}},w;(function(n){n.UserAuthentication="user-authentication",n.ChannelAuthorization="channel-authorization"})(w||(w={}));class u extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class h extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class k extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class T extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class m extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class M extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class X extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class ee extends Error{constructor(e){super(e),Object.setPrototypeOf(this,new.target.prototype)}}class V extends Error{constructor(e,t){super(t),this.status=e,Object.setPrototypeOf(this,new.target.prototype)}}var G=function(n,e,t,r,i){const s=A.createXHR();s.open("POST",t.endpoint,!0),s.setRequestHeader("Content-Type","application/x-www-form-urlencoded");for(var l in t.headers)s.setRequestHeader(l,t.headers[l]);if(t.headersProvider!=null){let b=t.headersProvider();for(var l in b)s.setRequestHeader(l,b[l])}return s.onreadystatechange=function(){if(s.readyState===4)if(s.status===200){let b,L=!1;try{b=JSON.parse(s.responseText),L=!0}catch{i(new V(200,`JSON returned from ${r.toString()} endpoint was invalid, yet status code was 200. Data was: ${s.responseText}`),null)}L&&i(null,b)}else{let b="";switch(r){case w.UserAuthentication:b=S.buildLogSuffix("authenticationEndpoint");break;case w.ChannelAuthorization:b=`Clients must be authorized to join private or presence channels. ${S.buildLogSuffix("authorizationEndpoint")}`;break}i(new V(s.status,`Unable to retrieve auth string from ${r.toString()} endpoint - received status: ${s.status} from ${t.endpoint}. ${b}`),null)}},s.send(e),s};function ke(n){return me(Me(n))}var ue=String.fromCharCode,se="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",Le=function(n){var e=n.charCodeAt(0);return e<128?n:e<2048?ue(192|e>>>6)+ue(128|e&63):ue(224|e>>>12&15)+ue(128|e>>>6&63)+ue(128|e&63)},Me=function(n){return n.replace(/[^\x00-\x7F]/g,Le)},te=function(n){var e=[0,2,1][n.length%3],t=n.charCodeAt(0)<<16|(n.length>1?n.charCodeAt(1):0)<<8|(n.length>2?n.charCodeAt(2):0),r=[se.charAt(t>>>18),se.charAt(t>>>12&63),e>=2?"=":se.charAt(t>>>6&63),e>=1?"=":se.charAt(t&63)];return r.join("")},me=window.btoa||function(n){return n.replace(/[\s\S]{1,3}/g,te)};class Ue{constructor(e,t,r,i){this.clear=t,this.timer=e(()=>{this.timer&&(this.timer=i(this.timer))},r)}isRunning(){return this.timer!==null}ensureAborted(){this.timer&&(this.clear(this.timer),this.timer=null)}}var Ae=Ue;function je(n){window.clearTimeout(n)}function ze(n){window.clearInterval(n)}class ie extends Ae{constructor(e,t){super(setTimeout,je,e,function(r){return t(),null})}}class Ee extends Ae{constructor(e,t){super(setInterval,ze,e,function(r){return t(),r})}}var oe={now(){return Date.now?Date.now():new Date().valueOf()},defer(n){return new ie(0,n)},method(n,...e){var t=Array.prototype.slice.call(arguments,1);return function(r){return r[n].apply(r,t.concat(arguments))}}},$=oe;function q(n,...e){for(var t=0;t<e.length;t++){var r=e[t];for(var i in r)r[i]&&r[i].constructor&&r[i].constructor===Object?n[i]=q(n[i]||{},r[i]):n[i]=r[i]}return n}function we(){for(var n=["Pusher"],e=0;e<arguments.length;e++)typeof arguments[e]=="string"?n.push(arguments[e]):n.push(Q(arguments[e]));return n.join(" : ")}function K(n,e){var t=Array.prototype.indexOf;if(n===null)return-1;if(t&&n.indexOf===t)return n.indexOf(e);for(var r=0,i=n.length;r<i;r++)if(n[r]===e)return r;return-1}function ne(n,e){for(var t in n)Object.prototype.hasOwnProperty.call(n,t)&&e(n[t],t,n)}function Pe(n){var e=[];return ne(n,function(t,r){e.push(r)}),e}function He(n){var e=[];return ne(n,function(t){e.push(t)}),e}function de(n,e,t){for(var r=0;r<n.length;r++)e.call(t||window,n[r],r,n)}function xe(n,e){for(var t=[],r=0;r<n.length;r++)t.push(e(n[r],r,n,t));return t}function qe(n,e){var t={};return ne(n,function(r,i){t[i]=e(r)}),t}function Se(n,e){e=e||function(i){return!!i};for(var t=[],r=0;r<n.length;r++)e(n[r],r,n,t)&&t.push(n[r]);return t}function o(n,e){var t={};return ne(n,function(r,i){(e&&e(r,i,n,t)||r)&&(t[i]=r)}),t}function a(n){var e=[];return ne(n,function(t,r){e.push([r,t])}),e}function E(n,e){for(var t=0;t<n.length;t++)if(e(n[t],t,n))return!0;return!1}function U(n,e){for(var t=0;t<n.length;t++)if(!e(n[t],t,n))return!1;return!0}function D(n){return qe(n,function(e){return typeof e=="object"&&(e=Q(e)),encodeURIComponent(ke(e.toString()))})}function j(n){var e=o(n,function(r){return r!==void 0}),t=xe(a(D(e)),$.method("join","=")).join("&");return t}function F(n){var e=[],t=[];return(function r(i,s){var l,b,L;switch(typeof i){case"object":if(!i)return null;for(l=0;l<e.length;l+=1)if(e[l]===i)return{$ref:t[l]};if(e.push(i),t.push(s),Object.prototype.toString.apply(i)==="[object Array]")for(L=[],l=0;l<i.length;l+=1)L[l]=r(i[l],s+"["+l+"]");else{L={};for(b in i)Object.prototype.hasOwnProperty.call(i,b)&&(L[b]=r(i[b],s+"["+JSON.stringify(b)+"]"))}return L;case"number":case"string":case"boolean":return i}})(n,"$")}function Q(n){try{return JSON.stringify(n)}catch{return JSON.stringify(F(n))}}class Y{constructor(){this.globalLog=e=>{window.console&&window.console.log&&window.console.log(e)}}debug(...e){this.log(this.globalLog,e)}warn(...e){this.log(this.globalLogWarn,e)}error(...e){this.log(this.globalLogError,e)}globalLogWarn(e){window.console&&window.console.warn?window.console.warn(e):this.globalLog(e)}globalLogError(e){window.console&&window.console.error?window.console.error(e):this.globalLogWarn(e)}log(e,...t){var r=we.apply(this,arguments);Ze.log?Ze.log(r):Ze.logToConsole&&e.bind(this)(r)}}var z=new Y,De=function(n,e,t,r,i){(t.headers!==void 0||t.headersProvider!=null)&&z.warn(`To send headers with the ${r.toString()} request, you must use AJAX, rather than JSONP.`);var s=n.nextAuthCallbackID.toString();n.nextAuthCallbackID++;var l=n.getDocument(),b=l.createElement("script");n.auth_callbacks[s]=function(R){i(null,R)};var L="Pusher.auth_callbacks['"+s+"']";b.src=t.endpoint+"?callback="+encodeURIComponent(L)+"&"+e;var O=l.getElementsByTagName("head")[0]||l.documentElement;O.insertBefore(b,O.firstChild)},It=De;class Ot{constructor(e){this.src=e}send(e){var t=this,r="Error loading "+t.src;t.script=document.createElement("script"),t.script.id=e.id,t.script.src=t.src,t.script.type="text/javascript",t.script.charset="UTF-8",t.script.addEventListener?(t.script.onerror=function(){e.callback(r)},t.script.onload=function(){e.callback(null)}):t.script.onreadystatechange=function(){(t.script.readyState==="loaded"||t.script.readyState==="complete")&&e.callback(null)},t.script.async===void 0&&document.attachEvent&&/opera/i.test(navigator.userAgent)?(t.errorScript=document.createElement("script"),t.errorScript.id=e.id+"_error",t.errorScript.text=e.name+"('"+r+"');",t.script.async=t.errorScript.async=!1):t.script.async=!0;var i=document.getElementsByTagName("head")[0];i.insertBefore(t.script,i.firstChild),t.errorScript&&i.insertBefore(t.errorScript,t.script.nextSibling)}cleanup(){this.script&&(this.script.onload=this.script.onerror=null,this.script.onreadystatechange=null),this.script&&this.script.parentNode&&this.script.parentNode.removeChild(this.script),this.errorScript&&this.errorScript.parentNode&&this.errorScript.parentNode.removeChild(this.errorScript),this.script=null,this.errorScript=null}}class Rt{constructor(e,t){this.url=e,this.data=t}send(e){if(!this.request){var t=j(this.data),r=this.url+"/"+e.number+"?"+t;this.request=A.createScriptRequest(r),this.request.send(e)}}cleanup(){this.request&&this.request.cleanup()}}var Nt=function(n,e){return function(t,r){var i="http"+(e?"s":"")+"://",s=i+(n.host||n.options.host)+n.options.path,l=A.createJSONPRequest(s,t),b=A.ScriptReceivers.create(function(L,O){c.remove(b),l.cleanup(),O&&O.host&&(n.host=O.host),r&&r(L,O)});l.send(b)}},Mt={name:"jsonp",getAgent:Nt},Ut=Mt;function Be(n,e,t){var r=n+(e.useTLS?"s":""),i=e.useTLS?e.hostTLS:e.hostNonTLS;return r+"://"+i+t}function $e(n,e){var t="/app/"+n,r="?protocol="+p.PROTOCOL+"&client=js&version="+p.VERSION+(e?"&"+e:"");return t+r}var jt={getInitial:function(n,e){var t=(e.httpPath||"")+$e(n,"flash=false");return Be("ws",e,t)}},zt={getInitial:function(n,e){var t=(e.httpPath||"/pusher")+$e(n);return Be("http",e,t)}},Ht={getInitial:function(n,e){return Be("http",e,e.httpPath||"/pusher")},getPath:function(n,e){return $e(n)}};class qt{constructor(){this._callbacks={}}get(e){return this._callbacks[Fe(e)]}add(e,t,r){var i=Fe(e);this._callbacks[i]=this._callbacks[i]||[],this._callbacks[i].push({fn:t,context:r})}remove(e,t,r){if(!e&&!t&&!r){this._callbacks={};return}var i=e?[Fe(e)]:Pe(this._callbacks);t||r?this.removeCallback(i,t,r):this.removeAllCallbacks(i)}removeCallback(e,t,r){de(e,function(i){this._callbacks[i]=Se(this._callbacks[i]||[],function(s){return t&&t!==s.fn||r&&r!==s.context}),this._callbacks[i].length===0&&delete this._callbacks[i]},this)}removeAllCallbacks(e){de(e,function(t){delete this._callbacks[t]},this)}}function Fe(n){return"_"+n}class ae{constructor(e){this.callbacks=new qt,this.global_callbacks=[],this.failThrough=e}bind(e,t,r){return this.callbacks.add(e,t,r),this}bind_global(e){return this.global_callbacks.push(e),this}unbind(e,t,r){return this.callbacks.remove(e,t,r),this}unbind_global(e){return e?(this.global_callbacks=Se(this.global_callbacks||[],t=>t!==e),this):(this.global_callbacks=[],this)}unbind_all(){return this.unbind(),this.unbind_global(),this}emit(e,t,r){for(var i=0;i<this.global_callbacks.length;i++)this.global_callbacks[i](e,t);var s=this.callbacks.get(e),l=[];if(r?l.push(t,r):t&&l.push(t),s&&s.length>0)for(var i=0;i<s.length;i++)s[i].fn.apply(s[i].context||window,l);else this.failThrough&&this.failThrough(e,t);return this}}class Dt extends ae{constructor(e,t,r,i,s){super(),this.initialize=A.transportConnectionInitializer,this.hooks=e,this.name=t,this.priority=r,this.key=i,this.options=s,this.state="new",this.timeline=s.timeline,this.activityTimeout=s.activityTimeout,this.id=this.timeline.generateUniqueID()}handlesActivityChecks(){return!!this.hooks.handlesActivityChecks}supportsPing(){return!!this.hooks.supportsPing}connect(){if(this.socket||this.state!=="initialized")return!1;var e=this.hooks.urls.getInitial(this.key,this.options);try{this.socket=this.hooks.getSocket(e,this.options)}catch(t){return $.defer(()=>{this.onError(t),this.changeState("closed")}),!1}return this.bindListeners(),z.debug("Connecting",{transport:this.name,url:e}),this.changeState("connecting"),!0}close(){return this.socket?(this.socket.close(),!0):!1}send(e){return this.state==="open"?($.defer(()=>{this.socket&&this.socket.send(e)}),!0):!1}ping(){this.state==="open"&&this.supportsPing()&&this.socket.ping()}onOpen(){this.hooks.beforeOpen&&this.hooks.beforeOpen(this.socket,this.hooks.urls.getPath(this.key,this.options)),this.changeState("open"),this.socket.onopen=void 0}onError(e){this.emit("error",{type:"WebSocketError",error:e}),this.timeline.error(this.buildTimelineMessage({error:e.toString()}))}onClose(e){e?this.changeState("closed",{code:e.code,reason:e.reason,wasClean:e.wasClean}):this.changeState("closed"),this.unbindListeners(),this.socket=void 0}onMessage(e){this.emit("message",e)}onActivity(){this.emit("activity")}bindListeners(){this.socket.onopen=()=>{this.onOpen()},this.socket.onerror=e=>{this.onError(e)},this.socket.onclose=e=>{this.onClose(e)},this.socket.onmessage=e=>{this.onMessage(e)},this.supportsPing()&&(this.socket.onactivity=()=>{this.onActivity()})}unbindListeners(){this.socket&&(this.socket.onopen=void 0,this.socket.onerror=void 0,this.socket.onclose=void 0,this.socket.onmessage=void 0,this.supportsPing()&&(this.socket.onactivity=void 0))}changeState(e,t){this.state=e,this.timeline.info(this.buildTimelineMessage({state:e,params:t})),this.emit(e,t)}buildTimelineMessage(e){return q({cid:this.id},e)}}class ve{constructor(e){this.hooks=e}isSupported(e){return this.hooks.isSupported(e)}createConnection(e,t,r,i){return new Dt(this.hooks,e,t,r,i)}}var Bt=new ve({urls:jt,handlesActivityChecks:!1,supportsPing:!1,isInitialized:function(){return!!A.getWebSocketAPI()},isSupported:function(){return!!A.getWebSocketAPI()},getSocket:function(n){return A.createWebSocket(n)}}),it={urls:zt,handlesActivityChecks:!1,supportsPing:!0,isInitialized:function(){return!0}},st=q({getSocket:function(n){return A.HTTPFactory.createStreamingSocket(n)}},it),ot=q({getSocket:function(n){return A.HTTPFactory.createPollingSocket(n)}},it),at={isSupported:function(){return A.isXHRSupported()}},$t=new ve(q({},st,at)),Ft=new ve(q({},ot,at)),Wt={ws:Bt,xhr_streaming:$t,xhr_polling:Ft},Ie=Wt,Xt=new ve({file:"sockjs",urls:Ht,handlesActivityChecks:!0,supportsPing:!1,isSupported:function(){return!0},isInitialized:function(){return window.SockJS!==void 0},getSocket:function(n,e){return new window.SockJS(n,null,{js_path:_.getPath("sockjs",{useTLS:e.useTLS}),ignore_null_origin:e.ignoreNullOrigin})},beforeOpen:function(n,e){n.send(JSON.stringify({path:e}))}}),ct={isSupported:function(n){var e=A.isXDRSupported(n.useTLS);return e}},Jt=new ve(q({},st,ct)),Vt=new ve(q({},ot,ct));Ie.xdr_streaming=Jt,Ie.xdr_polling=Vt,Ie.sockjs=Xt;var Gt=Ie;class Kt extends ae{constructor(){super();var e=this;window.addEventListener!==void 0&&(window.addEventListener("online",function(){e.emit("online")},!1),window.addEventListener("offline",function(){e.emit("offline")},!1))}isOnline(){return window.navigator.onLine===void 0?!0:window.navigator.onLine}}var Yt=new Kt;class Zt{constructor(e,t,r){this.manager=e,this.transport=t,this.minPingDelay=r.minPingDelay,this.maxPingDelay=r.maxPingDelay,this.pingDelay=void 0}createConnection(e,t,r,i){i=q({},i,{activityTimeout:this.pingDelay});var s=this.transport.createConnection(e,t,r,i),l=null,b=function(){s.unbind("open",b),s.bind("closed",L),l=$.now()},L=O=>{if(s.unbind("closed",L),O.code===1002||O.code===1003)this.manager.reportDeath();else if(!O.wasClean&&l){var R=$.now()-l;R<2*this.maxPingDelay&&(this.manager.reportDeath(),this.pingDelay=Math.max(R/2,this.minPingDelay))}};return s.bind("open",b),s}isSupported(e){return this.manager.isAlive()&&this.transport.isSupported(e)}}const lt={decodeMessage:function(n){try{var e=JSON.parse(n.data),t=e.data;if(typeof t=="string")try{t=JSON.parse(e.data)}catch{}var r={event:e.event,channel:e.channel,data:t};return e.user_id&&(r.user_id=e.user_id),r}catch(i){throw{type:"MessageParseError",error:i,data:n.data}}},encodeMessage:function(n){return JSON.stringify(n)},processHandshake:function(n){var e=lt.decodeMessage(n);if(e.event==="pusher:connection_established"){if(!e.data.activity_timeout)throw"No activity timeout specified in handshake";return{action:"connected",id:e.data.socket_id,activityTimeout:e.data.activity_timeout*1e3}}else{if(e.event==="pusher:error")return{action:this.getCloseAction(e.data),error:this.getCloseError(e.data)};throw"Invalid handshake"}},getCloseAction:function(n){return n.code<4e3?n.code>=1002&&n.code<=1004?"backoff":null:n.code===4e3?"tls_only":n.code<4100?"refused":n.code<4200?"backoff":n.code<4300?"retry":"refused"},getCloseError:function(n){return n.code!==1e3&&n.code!==1001?{type:"PusherError",data:{code:n.code,message:n.reason||n.message}}:null}};var he=lt;class Qt extends ae{constructor(e,t){super(),this.id=e,this.transport=t,this.activityTimeout=t.activityTimeout,this.bindListeners()}handlesActivityChecks(){return this.transport.handlesActivityChecks()}send(e){return this.transport.send(e)}send_event(e,t,r){var i={event:e,data:t};return r&&(i.channel=r),z.debug("Event sent",i),this.send(he.encodeMessage(i))}ping(){this.transport.supportsPing()?this.transport.ping():this.send_event("pusher:ping",{})}close(){this.transport.close()}bindListeners(){var e={message:r=>{var i;try{i=he.decodeMessage(r)}catch(s){this.emit("error",{type:"MessageParseError",error:s,data:r.data})}if(i!==void 0){switch(z.debug("Event recd",i),i.event){case"pusher:error":this.emit("error",{type:"PusherError",data:i.data});break;case"pusher:ping":this.emit("ping");break;case"pusher:pong":this.emit("pong");break}this.emit("message",i)}},activity:()=>{this.emit("activity")},error:r=>{this.emit("error",r)},closed:r=>{t(),r&&r.code&&this.handleCloseEvent(r),this.transport=null,this.emit("closed")}},t=()=>{ne(e,(r,i)=>{this.transport.unbind(i,r)})};ne(e,(r,i)=>{this.transport.bind(i,r)})}handleCloseEvent(e){var t=he.getCloseAction(e),r=he.getCloseError(e);r&&this.emit("error",r),t&&this.emit(t,{action:t,error:r})}}class en{constructor(e,t){this.transport=e,this.callback=t,this.bindListeners()}close(){this.unbindListeners(),this.transport.close()}bindListeners(){this.onMessage=e=>{this.unbindListeners();var t;try{t=he.processHandshake(e)}catch(r){this.finish("error",{error:r}),this.transport.close();return}t.action==="connected"?this.finish("connected",{connection:new Qt(t.id,this.transport),activityTimeout:t.activityTimeout}):(this.finish(t.action,{error:t.error}),this.transport.close())},this.onClosed=e=>{this.unbindListeners();var t=he.getCloseAction(e)||"backoff",r=he.getCloseError(e);this.finish(t,{error:r})},this.transport.bind("message",this.onMessage),this.transport.bind("closed",this.onClosed)}unbindListeners(){this.transport.unbind("message",this.onMessage),this.transport.unbind("closed",this.onClosed)}finish(e,t){this.callback(q({transport:this.transport,action:e},t))}}class tn{constructor(e,t){this.timeline=e,this.options=t||{}}send(e,t){this.timeline.isEmpty()||this.timeline.send(A.TimelineTransport.getAgent(this,e),t)}}class We extends ae{constructor(e,t){super(function(r,i){z.debug("No callbacks on "+e+" for "+r)}),this.name=e,this.pusher=t,this.subscribed=!1,this.subscriptionPending=!1,this.subscriptionCancelled=!1}authorize(e,t){return t(null,{auth:""})}trigger(e,t){if(e.indexOf("client-")!==0)throw new u("Event '"+e+"' does not start with 'client-'");if(!this.subscribed){var r=S.buildLogSuffix("triggeringClientEvents");z.warn(`Client event triggered before channel 'subscription_succeeded' event . ${r}`)}return this.pusher.send_event(e,t,this.name)}disconnect(){this.subscribed=!1,this.subscriptionPending=!1}handleEvent(e){var t=e.event,r=e.data;if(t==="pusher_internal:subscription_succeeded")this.handleSubscriptionSucceededEvent(e);else if(t==="pusher_internal:subscription_count")this.handleSubscriptionCountEvent(e);else if(t.indexOf("pusher_internal:")!==0){var i={};this.emit(t,r,i)}}handleSubscriptionSucceededEvent(e){this.subscriptionPending=!1,this.subscribed=!0,this.subscriptionCancelled?this.pusher.unsubscribe(this.name):this.emit("pusher:subscription_succeeded",e.data)}handleSubscriptionCountEvent(e){e.data.subscription_count&&(this.subscriptionCount=e.data.subscription_count),this.emit("pusher:subscription_count",e.data)}subscribe(){this.subscribed||(this.subscriptionPending=!0,this.subscriptionCancelled=!1,this.authorize(this.pusher.connection.socket_id,(e,t)=>{e?(this.subscriptionPending=!1,z.error(e.toString()),this.emit("pusher:subscription_error",Object.assign({},{type:"AuthError",error:e.message},e instanceof V?{status:e.status}:{}))):this.pusher.send_event("pusher:subscribe",{auth:t.auth,channel_data:t.channel_data,channel:this.name})}))}unsubscribe(){this.subscribed=!1,this.pusher.send_event("pusher:unsubscribe",{channel:this.name})}cancelSubscription(){this.subscriptionCancelled=!0}reinstateSubscription(){this.subscriptionCancelled=!1}}class Xe extends We{authorize(e,t){return this.pusher.config.channelAuthorizer({channelName:this.name,socketId:e},t)}}class nn{constructor(){this.reset()}get(e){return Object.prototype.hasOwnProperty.call(this.members,e)?{id:e,info:this.members[e]}:null}each(e){ne(this.members,(t,r)=>{e(this.get(r))})}setMyID(e){this.myID=e}onSubscription(e){this.members=e.presence.hash,this.count=e.presence.count,this.me=this.get(this.myID)}addMember(e){return this.get(e.user_id)===null&&this.count++,this.members[e.user_id]=e.user_info,this.get(e.user_id)}removeMember(e){var t=this.get(e.user_id);return t&&(delete this.members[e.user_id],this.count--),t}reset(){this.members={},this.count=0,this.myID=null,this.me=null}}var rn=function(n,e,t,r){function i(s){return s instanceof t?s:new t(function(l){l(s)})}return new(t||(t=Promise))(function(s,l){function b(R){try{O(r.next(R))}catch(B){l(B)}}function L(R){try{O(r.throw(R))}catch(B){l(B)}}function O(R){R.done?s(R.value):i(R.value).then(b,L)}O((r=r.apply(n,e||[])).next())})};class sn extends Xe{constructor(e,t){super(e,t),this.members=new nn}authorize(e,t){super.authorize(e,(r,i)=>rn(this,void 0,void 0,function*(){if(!r)if(i=i,i.channel_data!=null){var s=JSON.parse(i.channel_data);this.members.setMyID(s.user_id)}else if(yield this.pusher.user.signinDonePromise,this.pusher.user.user_data!=null)this.members.setMyID(this.pusher.user.user_data.id);else{let l=S.buildLogSuffix("authorizationEndpoint");z.error(`Invalid auth response for channel '${this.name}', expected 'channel_data' field. ${l}, or the user should be signed in.`),t("Invalid auth response");return}t(r,i)}))}handleEvent(e){var t=e.event;if(t.indexOf("pusher_internal:")===0)this.handleInternalEvent(e);else{var r=e.data,i={};e.user_id&&(i.user_id=e.user_id),this.emit(t,r,i)}}handleInternalEvent(e){var t=e.event,r=e.data;switch(t){case"pusher_internal:subscription_succeeded":this.handleSubscriptionSucceededEvent(e);break;case"pusher_internal:subscription_count":this.handleSubscriptionCountEvent(e);break;case"pusher_internal:member_added":var i=this.members.addMember(r);this.emit("pusher:member_added",i);break;case"pusher_internal:member_removed":var s=this.members.removeMember(r);s&&this.emit("pusher:member_removed",s);break}}handleSubscriptionSucceededEvent(e){this.subscriptionPending=!1,this.subscribed=!0,this.subscriptionCancelled?this.pusher.unsubscribe(this.name):(this.members.onSubscription(e.data),this.emit("pusher:subscription_succeeded",this.members))}disconnect(){this.members.reset(),super.disconnect()}}var on=C(1),Je=C(0);class an extends Xe{constructor(e,t,r){super(e,t),this.key=null,this.nacl=r}authorize(e,t){super.authorize(e,(r,i)=>{if(r){t(r,i);return}let s=i.shared_secret;if(!s){t(new Error(`No shared_secret key in auth payload for encrypted channel: ${this.name}`),null);return}this.key=Object(Je.decode)(s),delete i.shared_secret,t(null,i)})}trigger(e,t){throw new M("Client events are not currently supported for encrypted channels")}handleEvent(e){var t=e.event,r=e.data;if(t.indexOf("pusher_internal:")===0||t.indexOf("pusher:")===0){super.handleEvent(e);return}this.handleEncryptedEvent(t,r)}handleEncryptedEvent(e,t){if(!this.key){z.debug("Received encrypted event before key has been retrieved from the authEndpoint");return}if(!t.ciphertext||!t.nonce){z.error("Unexpected format for encrypted event, expected object with `ciphertext` and `nonce` fields, got: "+t);return}let r=Object(Je.decode)(t.ciphertext);if(r.length<this.nacl.secretbox.overheadLength){z.error(`Expected encrypted event ciphertext length to be ${this.nacl.secretbox.overheadLength}, got: ${r.length}`);return}let i=Object(Je.decode)(t.nonce);if(i.length<this.nacl.secretbox.nonceLength){z.error(`Expected encrypted event nonce length to be ${this.nacl.secretbox.nonceLength}, got: ${i.length}`);return}let s=this.nacl.secretbox.open(r,i,this.key);if(s===null){z.debug("Failed to decrypt an event, probably because it was encrypted with a different key. Fetching a new key from the authEndpoint..."),this.authorize(this.pusher.connection.socket_id,(l,b)=>{if(l){z.error(`Failed to make a request to the authEndpoint: ${b}. Unable to fetch new key, so dropping encrypted event`);return}if(s=this.nacl.secretbox.open(r,i,this.key),s===null){z.error("Failed to decrypt event with new key. Dropping encrypted event");return}this.emit(e,this.getDataToEmit(s))});return}this.emit(e,this.getDataToEmit(s))}getDataToEmit(e){let t=Object(on.decode)(e);try{return JSON.parse(t)}catch{return t}}}class cn extends ae{constructor(e,t){super(),this.state="initialized",this.connection=null,this.key=e,this.options=t,this.timeline=this.options.timeline,this.usingTLS=this.options.useTLS,this.errorCallbacks=this.buildErrorCallbacks(),this.connectionCallbacks=this.buildConnectionCallbacks(this.errorCallbacks),this.handshakeCallbacks=this.buildHandshakeCallbacks(this.errorCallbacks);var r=A.getNetwork();r.bind("online",()=>{this.timeline.info({netinfo:"online"}),(this.state==="connecting"||this.state==="unavailable")&&this.retryIn(0)}),r.bind("offline",()=>{this.timeline.info({netinfo:"offline"}),this.connection&&this.sendActivityCheck()}),this.updateStrategy()}connect(){if(!(this.connection||this.runner)){if(!this.strategy.isSupported()){this.updateState("failed");return}this.updateState("connecting"),this.startConnecting(),this.setUnavailableTimer()}}send(e){return this.connection?this.connection.send(e):!1}send_event(e,t,r){return this.connection?this.connection.send_event(e,t,r):!1}disconnect(){this.disconnectInternally(),this.updateState("disconnected")}isUsingTLS(){return this.usingTLS}startConnecting(){var e=(t,r)=>{t?this.runner=this.strategy.connect(0,e):r.action==="error"?(this.emit("error",{type:"HandshakeError",error:r.error}),this.timeline.error({handshakeError:r.error})):(this.abortConnecting(),this.handshakeCallbacks[r.action](r))};this.runner=this.strategy.connect(0,e)}abortConnecting(){this.runner&&(this.runner.abort(),this.runner=null)}disconnectInternally(){if(this.abortConnecting(),this.clearRetryTimer(),this.clearUnavailableTimer(),this.connection){var e=this.abandonConnection();e.close()}}updateStrategy(){this.strategy=this.options.getStrategy({key:this.key,timeline:this.timeline,useTLS:this.usingTLS})}retryIn(e){this.timeline.info({action:"retry",delay:e}),e>0&&this.emit("connecting_in",Math.round(e/1e3)),this.retryTimer=new ie(e||0,()=>{this.disconnectInternally(),this.connect()})}clearRetryTimer(){this.retryTimer&&(this.retryTimer.ensureAborted(),this.retryTimer=null)}setUnavailableTimer(){this.unavailableTimer=new ie(this.options.unavailableTimeout,()=>{this.updateState("unavailable")})}clearUnavailableTimer(){this.unavailableTimer&&this.unavailableTimer.ensureAborted()}sendActivityCheck(){this.stopActivityCheck(),this.connection.ping(),this.activityTimer=new ie(this.options.pongTimeout,()=>{this.timeline.error({pong_timed_out:this.options.pongTimeout}),this.retryIn(0)})}resetActivityCheck(){this.stopActivityCheck(),this.connection&&!this.connection.handlesActivityChecks()&&(this.activityTimer=new ie(this.activityTimeout,()=>{this.sendActivityCheck()}))}stopActivityCheck(){this.activityTimer&&this.activityTimer.ensureAborted()}buildConnectionCallbacks(e){return q({},e,{message:t=>{this.resetActivityCheck(),this.emit("message",t)},ping:()=>{this.send_event("pusher:pong",{})},activity:()=>{this.resetActivityCheck()},error:t=>{this.emit("error",t)},closed:()=>{this.abandonConnection(),this.shouldRetry()&&this.retryIn(1e3)}})}buildHandshakeCallbacks(e){return q({},e,{connected:t=>{this.activityTimeout=Math.min(this.options.activityTimeout,t.activityTimeout,t.connection.activityTimeout||1/0),this.clearUnavailableTimer(),this.setConnection(t.connection),this.socket_id=this.connection.id,this.updateState("connected",{socket_id:this.socket_id})}})}buildErrorCallbacks(){let e=t=>r=>{r.error&&this.emit("error",{type:"WebSocketError",error:r.error}),t(r)};return{tls_only:e(()=>{this.usingTLS=!0,this.updateStrategy(),this.retryIn(0)}),refused:e(()=>{this.disconnect()}),backoff:e(()=>{this.retryIn(1e3)}),retry:e(()=>{this.retryIn(0)})}}setConnection(e){this.connection=e;for(var t in this.connectionCallbacks)this.connection.bind(t,this.connectionCallbacks[t]);this.resetActivityCheck()}abandonConnection(){if(this.connection){this.stopActivityCheck();for(var e in this.connectionCallbacks)this.connection.unbind(e,this.connectionCallbacks[e]);var t=this.connection;return this.connection=null,t}}updateState(e,t){var r=this.state;if(this.state=e,r!==e){var i=e;i==="connected"&&(i+=" with new socket ID "+t.socket_id),z.debug("State changed",r+" -> "+i),this.timeline.info({state:e,params:t}),this.emit("state_change",{previous:r,current:e}),this.emit(e,t)}}shouldRetry(){return this.state==="connecting"||this.state==="connected"}}class ln{constructor(){this.channels={}}add(e,t){return this.channels[e]||(this.channels[e]=un(e,t)),this.channels[e]}all(){return He(this.channels)}find(e){return this.channels[e]}remove(e){var t=this.channels[e];return delete this.channels[e],t}disconnect(){ne(this.channels,function(e){e.disconnect()})}}function un(n,e){if(n.indexOf("private-encrypted-")===0){if(e.config.nacl)return ce.createEncryptedChannel(n,e,e.config.nacl);let t="Tried to subscribe to a private-encrypted- channel but no nacl implementation available",r=S.buildLogSuffix("encryptedChannelSupport");throw new M(`${t}. ${r}`)}else{if(n.indexOf("private-")===0)return ce.createPrivateChannel(n,e);if(n.indexOf("presence-")===0)return ce.createPresenceChannel(n,e);if(n.indexOf("#")===0)throw new h('Cannot create a channel with name "'+n+'".');return ce.createChannel(n,e)}}var dn={createChannels(){return new ln},createConnectionManager(n,e){return new cn(n,e)},createChannel(n,e){return new We(n,e)},createPrivateChannel(n,e){return new Xe(n,e)},createPresenceChannel(n,e){return new sn(n,e)},createEncryptedChannel(n,e,t){return new an(n,e,t)},createTimelineSender(n,e){return new tn(n,e)},createHandshake(n,e){return new en(n,e)},createAssistantToTheTransportManager(n,e,t){return new Zt(n,e,t)}},ce=dn;class ut{constructor(e){this.options=e||{},this.livesLeft=this.options.lives||1/0}getAssistant(e){return ce.createAssistantToTheTransportManager(this,e,{minPingDelay:this.options.minPingDelay,maxPingDelay:this.options.maxPingDelay})}isAlive(){return this.livesLeft>0}reportDeath(){this.livesLeft-=1}}class pe{constructor(e,t){this.strategies=e,this.loop=!!t.loop,this.failFast=!!t.failFast,this.timeout=t.timeout,this.timeoutLimit=t.timeoutLimit}isSupported(){return E(this.strategies,$.method("isSupported"))}connect(e,t){var r=this.strategies,i=0,s=this.timeout,l=null,b=(L,O)=>{O?t(null,O):(i=i+1,this.loop&&(i=i%r.length),i<r.length?(s&&(s=s*2,this.timeoutLimit&&(s=Math.min(s,this.timeoutLimit))),l=this.tryStrategy(r[i],e,{timeout:s,failFast:this.failFast},b)):t(!0))};return l=this.tryStrategy(r[i],e,{timeout:s,failFast:this.failFast},b),{abort:function(){l.abort()},forceMinPriority:function(L){e=L,l&&l.forceMinPriority(L)}}}tryStrategy(e,t,r,i){var s=null,l=null;return r.timeout>0&&(s=new ie(r.timeout,function(){l.abort(),i(!0)})),l=e.connect(t,function(b,L){b&&s&&s.isRunning()&&!r.failFast||(s&&s.ensureAborted(),i(b,L))}),{abort:function(){s&&s.ensureAborted(),l.abort()},forceMinPriority:function(b){l.forceMinPriority(b)}}}}class Ve{constructor(e){this.strategies=e}isSupported(){return E(this.strategies,$.method("isSupported"))}connect(e,t){return hn(this.strategies,e,function(r,i){return function(s,l){if(i[r].error=s,s){pn(i)&&t(!0);return}de(i,function(b){b.forceMinPriority(l.transport.priority)}),t(null,l)}})}}function hn(n,e,t){var r=xe(n,function(i,s,l,b){return i.connect(e,t(s,b))});return{abort:function(){de(r,fn)},forceMinPriority:function(i){de(r,function(s){s.forceMinPriority(i)})}}}function pn(n){return U(n,function(e){return!!e.error})}function fn(n){!n.error&&!n.aborted&&(n.abort(),n.aborted=!0)}class gn{constructor(e,t,r){this.strategy=e,this.transports=t,this.ttl=r.ttl||1800*1e3,this.usingTLS=r.useTLS,this.timeline=r.timeline}isSupported(){return this.strategy.isSupported()}connect(e,t){var r=this.usingTLS,i=mn(r),s=i&&i.cacheSkipCount?i.cacheSkipCount:0,l=[this.strategy];if(i&&i.timestamp+this.ttl>=$.now()){var b=this.transports[i.transport];b&&(["ws","wss"].includes(i.transport)||s>3?(this.timeline.info({cached:!0,transport:i.transport,latency:i.latency}),l.push(new pe([b],{timeout:i.latency*2+1e3,failFast:!0}))):s++)}var L=$.now(),O=l.pop().connect(e,function R(B,Ne){B?(dt(r),l.length>0?(L=$.now(),O=l.pop().connect(e,R)):t(B)):(vn(r,Ne.transport.name,$.now()-L,s),t(null,Ne))});return{abort:function(){O.abort()},forceMinPriority:function(R){e=R,O&&O.forceMinPriority(R)}}}}function Ge(n){return"pusherTransport"+(n?"TLS":"NonTLS")}function mn(n){var e=A.getLocalStorage();if(e)try{var t=e[Ge(n)];if(t)return JSON.parse(t)}catch{dt(n)}return null}function vn(n,e,t,r){var i=A.getLocalStorage();if(i)try{i[Ge(n)]=Q({timestamp:$.now(),transport:e,latency:t,cacheSkipCount:r})}catch{}}function dt(n){var e=A.getLocalStorage();if(e)try{delete e[Ge(n)]}catch{}}class Oe{constructor(e,{delay:t}){this.strategy=e,this.options={delay:t}}isSupported(){return this.strategy.isSupported()}connect(e,t){var r=this.strategy,i,s=new ie(this.options.delay,function(){i=r.connect(e,t)});return{abort:function(){s.ensureAborted(),i&&i.abort()},forceMinPriority:function(l){e=l,i&&i.forceMinPriority(l)}}}}class _e{constructor(e,t,r){this.test=e,this.trueBranch=t,this.falseBranch=r}isSupported(){var e=this.test()?this.trueBranch:this.falseBranch;return e.isSupported()}connect(e,t){var r=this.test()?this.trueBranch:this.falseBranch;return r.connect(e,t)}}class bn{constructor(e){this.strategy=e}isSupported(){return this.strategy.isSupported()}connect(e,t){var r=this.strategy.connect(e,function(i,s){s&&r.abort(),t(i,s)});return r}}function Ce(n){return function(){return n.isSupported()}}var yn=function(n,e,t){var r={};function i(St,br,yr,wr,xr){var _t=t(n,St,br,yr,wr,xr);return r[St]=_t,_t}var s=Object.assign({},e,{hostNonTLS:n.wsHost+":"+n.wsPort,hostTLS:n.wsHost+":"+n.wssPort,httpPath:n.wsPath}),l=Object.assign({},s,{useTLS:!0}),b=Object.assign({},e,{hostNonTLS:n.httpHost+":"+n.httpPort,hostTLS:n.httpHost+":"+n.httpsPort,httpPath:n.httpPath}),L={loop:!0,timeout:15e3,timeoutLimit:6e4},O=new ut({minPingDelay:1e4,maxPingDelay:n.activityTimeout}),R=new ut({lives:2,minPingDelay:1e4,maxPingDelay:n.activityTimeout}),B=i("ws","ws",3,s,O),Ne=i("wss","ws",3,l,O),pr=i("sockjs","sockjs",1,b),mt=i("xhr_streaming","xhr_streaming",1,b,R),fr=i("xdr_streaming","xdr_streaming",1,b,R),vt=i("xhr_polling","xhr_polling",1,b),gr=i("xdr_polling","xdr_polling",1,b),bt=new pe([B],L),mr=new pe([Ne],L),vr=new pe([pr],L),yt=new pe([new _e(Ce(mt),mt,fr)],L),wt=new pe([new _e(Ce(vt),vt,gr)],L),xt=new pe([new _e(Ce(yt),new Ve([yt,new Oe(wt,{delay:4e3})]),wt)],L),Qe=new _e(Ce(xt),xt,vr),et;return e.useTLS?et=new Ve([bt,new Oe(Qe,{delay:2e3})]):et=new Ve([bt,new Oe(mr,{delay:2e3}),new Oe(Qe,{delay:5e3})]),new gn(new bn(new _e(Ce(B),et,Qe)),r,{ttl:18e5,timeline:e.timeline,useTLS:e.useTLS})},wn=yn,xn=(function(){var n=this;n.timeline.info(n.buildTimelineMessage({transport:n.name+(n.options.useTLS?"s":"")})),n.hooks.isInitialized()?n.changeState("initialized"):n.hooks.file?(n.changeState("initializing"),_.load(n.hooks.file,{useTLS:n.options.useTLS},function(e,t){n.hooks.isInitialized()?(n.changeState("initialized"),t(!0)):(e&&n.onError(e),n.onClose(),t(!1))})):n.onClose()}),Sn={getRequest:function(n){var e=new window.XDomainRequest;return e.ontimeout=function(){n.emit("error",new k),n.close()},e.onerror=function(t){n.emit("error",t),n.close()},e.onprogress=function(){e.responseText&&e.responseText.length>0&&n.onChunk(200,e.responseText)},e.onload=function(){e.responseText&&e.responseText.length>0&&n.onChunk(200,e.responseText),n.emit("finished",200),n.close()},e},abortRequest:function(n){n.ontimeout=n.onerror=n.onprogress=n.onload=null,n.abort()}},_n=Sn;const Cn=256*1024;class Tn extends ae{constructor(e,t,r){super(),this.hooks=e,this.method=t,this.url=r}start(e){this.position=0,this.xhr=this.hooks.getRequest(this),this.unloader=()=>{this.close()},A.addUnloadListener(this.unloader),this.xhr.open(this.method,this.url,!0),this.xhr.setRequestHeader&&this.xhr.setRequestHeader("Content-Type","application/json"),this.xhr.send(e)}close(){this.unloader&&(A.removeUnloadListener(this.unloader),this.unloader=null),this.xhr&&(this.hooks.abortRequest(this.xhr),this.xhr=null)}onChunk(e,t){for(;;){var r=this.advanceBuffer(t);if(r)this.emit("chunk",{status:e,data:r});else break}this.isBufferTooLong(t)&&this.emit("buffer_too_long")}advanceBuffer(e){var t=e.slice(this.position),r=t.indexOf(`
`);return r!==-1?(this.position+=r+1,t.slice(0,r)):null}isBufferTooLong(e){return this.position===e.length&&e.length>Cn}}var Ke;(function(n){n[n.CONNECTING=0]="CONNECTING",n[n.OPEN=1]="OPEN",n[n.CLOSED=3]="CLOSED"})(Ke||(Ke={}));var fe=Ke,kn=1;class Ln{constructor(e,t){this.hooks=e,this.session=pt(1e3)+"/"+In(8),this.location=An(t),this.readyState=fe.CONNECTING,this.openStream()}send(e){return this.sendRaw(JSON.stringify([e]))}ping(){this.hooks.sendHeartbeat(this)}close(e,t){this.onClose(e,t,!0)}sendRaw(e){if(this.readyState===fe.OPEN)try{return A.createSocketRequest("POST",ht(En(this.location,this.session))).start(e),!0}catch{return!1}else return!1}reconnect(){this.closeStream(),this.openStream()}onClose(e,t,r){this.closeStream(),this.readyState=fe.CLOSED,this.onclose&&this.onclose({code:e,reason:t,wasClean:r})}onChunk(e){if(e.status===200){this.readyState===fe.OPEN&&this.onActivity();var t,r=e.data.slice(0,1);switch(r){case"o":t=JSON.parse(e.data.slice(1)||"{}"),this.onOpen(t);break;case"a":t=JSON.parse(e.data.slice(1)||"[]");for(var i=0;i<t.length;i++)this.onEvent(t[i]);break;case"m":t=JSON.parse(e.data.slice(1)||"null"),this.onEvent(t);break;case"h":this.hooks.onHeartbeat(this);break;case"c":t=JSON.parse(e.data.slice(1)||"[]"),this.onClose(t[0],t[1],!0);break}}}onOpen(e){this.readyState===fe.CONNECTING?(e&&e.hostname&&(this.location.base=Pn(this.location.base,e.hostname)),this.readyState=fe.OPEN,this.onopen&&this.onopen()):this.onClose(1006,"Server lost session",!0)}onEvent(e){this.readyState===fe.OPEN&&this.onmessage&&this.onmessage({data:e})}onActivity(){this.onactivity&&this.onactivity()}onError(e){this.onerror&&this.onerror(e)}openStream(){this.stream=A.createSocketRequest("POST",ht(this.hooks.getReceiveURL(this.location,this.session))),this.stream.bind("chunk",e=>{this.onChunk(e)}),this.stream.bind("finished",e=>{this.hooks.onFinished(this,e)}),this.stream.bind("buffer_too_long",()=>{this.reconnect()});try{this.stream.start()}catch(e){$.defer(()=>{this.onError(e),this.onClose(1006,"Could not start streaming",!1)})}}closeStream(){this.stream&&(this.stream.unbind_all(),this.stream.close(),this.stream=null)}}function An(n){var e=/([^\?]*)\/*(\??.*)/.exec(n);return{base:e[1],queryString:e[2]}}function En(n,e){return n.base+"/"+e+"/xhr_send"}function ht(n){var e=n.indexOf("?")===-1?"?":"&";return n+e+"t="+ +new Date+"&n="+kn++}function Pn(n,e){var t=/(https?:\/\/)([^\/:]+)((\/|:)?.*)/.exec(n);return t[1]+e+t[3]}function pt(n){return A.randomInt(n)}function In(n){for(var e=[],t=0;t<n;t++)e.push(pt(32).toString(32));return e.join("")}var On=Ln,Rn={getReceiveURL:function(n,e){return n.base+"/"+e+"/xhr_streaming"+n.queryString},onHeartbeat:function(n){n.sendRaw("[]")},sendHeartbeat:function(n){n.sendRaw("[]")},onFinished:function(n,e){n.onClose(1006,"Connection interrupted ("+e+")",!1)}},Nn=Rn,Mn={getReceiveURL:function(n,e){return n.base+"/"+e+"/xhr"+n.queryString},onHeartbeat:function(){},sendHeartbeat:function(n){n.sendRaw("[]")},onFinished:function(n,e){e===200?n.reconnect():n.onClose(1006,"Connection interrupted ("+e+")",!1)}},Un=Mn,jn={getRequest:function(n){var e=A.getXHRAPI(),t=new e;return t.onreadystatechange=t.onprogress=function(){switch(t.readyState){case 3:t.responseText&&t.responseText.length>0&&n.onChunk(t.status,t.responseText);break;case 4:t.responseText&&t.responseText.length>0&&n.onChunk(t.status,t.responseText),n.emit("finished",t.status),n.close();break}},t},abortRequest:function(n){n.onreadystatechange=null,n.abort()}},zn=jn,Hn={createStreamingSocket(n){return this.createSocket(Nn,n)},createPollingSocket(n){return this.createSocket(Un,n)},createSocket(n,e){return new On(n,e)},createXHR(n,e){return this.createRequest(zn,n,e)},createRequest(n,e,t){return new Tn(n,e,t)}},ft=Hn;ft.createXDR=function(n,e){return this.createRequest(_n,n,e)};var qn=ft,Dn={nextAuthCallbackID:1,auth_callbacks:{},ScriptReceivers:c,DependenciesReceivers:f,getDefaultStrategy:wn,Transports:Gt,transportConnectionInitializer:xn,HTTPFactory:qn,TimelineTransport:Ut,getXHRAPI(){return window.XMLHttpRequest},getWebSocketAPI(){return window.WebSocket||window.MozWebSocket},setup(n){window.Pusher=n;var e=()=>{this.onDocumentBody(n.ready)};window.JSON?e():_.load("json2",{},e)},getDocument(){return document},getProtocol(){return this.getDocument().location.protocol},getAuthorizers(){return{ajax:G,jsonp:It}},onDocumentBody(n){document.body?n():setTimeout(()=>{this.onDocumentBody(n)},0)},createJSONPRequest(n,e){return new Rt(n,e)},createScriptRequest(n){return new Ot(n)},getLocalStorage(){try{return window.localStorage}catch{return}},createXHR(){return this.getXHRAPI()?this.createXMLHttpRequest():this.createMicrosoftXHR()},createXMLHttpRequest(){var n=this.getXHRAPI();return new n},createMicrosoftXHR(){return new ActiveXObject("Microsoft.XMLHTTP")},getNetwork(){return Yt},createWebSocket(n){var e=this.getWebSocketAPI();return new e(n)},createSocketRequest(n,e){if(this.isXHRSupported())return this.HTTPFactory.createXHR(n,e);if(this.isXDRSupported(e.indexOf("https:")===0))return this.HTTPFactory.createXDR(n,e);throw"Cross-origin HTTP requests are not supported"},isXHRSupported(){var n=this.getXHRAPI();return!!n&&new n().withCredentials!==void 0},isXDRSupported(n){var e=n?"https:":"http:",t=this.getProtocol();return!!window.XDomainRequest&&t===e},addUnloadListener(n){window.addEventListener!==void 0?window.addEventListener("unload",n,!1):window.attachEvent!==void 0&&window.attachEvent("onunload",n)},removeUnloadListener(n){window.addEventListener!==void 0?window.removeEventListener("unload",n,!1):window.detachEvent!==void 0&&window.detachEvent("onunload",n)},randomInt(n){return Math.floor(function(){return(window.crypto||window.msCrypto).getRandomValues(new Uint32Array(1))[0]/Math.pow(2,32)}()*n)}},A=Dn,Ye;(function(n){n[n.ERROR=3]="ERROR",n[n.INFO=6]="INFO",n[n.DEBUG=7]="DEBUG"})(Ye||(Ye={}));var Re=Ye;class Bn{constructor(e,t,r){this.key=e,this.session=t,this.events=[],this.options=r||{},this.sent=0,this.uniqueID=0}log(e,t){e<=this.options.level&&(this.events.push(q({},t,{timestamp:$.now()})),this.options.limit&&this.events.length>this.options.limit&&this.events.shift())}error(e){this.log(Re.ERROR,e)}info(e){this.log(Re.INFO,e)}debug(e){this.log(Re.DEBUG,e)}isEmpty(){return this.events.length===0}send(e,t){var r=q({session:this.session,bundle:this.sent+1,key:this.key,lib:"js",version:this.options.version,cluster:this.options.cluster,features:this.options.features,timeline:this.events},this.options.params);return this.events=[],e(r,(i,s)=>{i||this.sent++,t&&t(i,s)}),!0}generateUniqueID(){return this.uniqueID++,this.uniqueID}}class $n{constructor(e,t,r,i){this.name=e,this.priority=t,this.transport=r,this.options=i||{}}isSupported(){return this.transport.isSupported({useTLS:this.options.useTLS})}connect(e,t){if(this.isSupported()){if(this.priority<e)return gt(new T,t)}else return gt(new ee,t);var r=!1,i=this.transport.createConnection(this.name,this.priority,this.options.key,this.options),s=null,l=function(){i.unbind("initialized",l),i.connect()},b=function(){s=ce.createHandshake(i,function(B){r=!0,R(),t(null,B)})},L=function(B){R(),t(B)},O=function(){R();var B;B=Q(i),t(new m(B))},R=function(){i.unbind("initialized",l),i.unbind("open",b),i.unbind("error",L),i.unbind("closed",O)};return i.bind("initialized",l),i.bind("open",b),i.bind("error",L),i.bind("closed",O),i.initialize(),{abort:()=>{r||(R(),s?s.close():i.close())},forceMinPriority:B=>{r||this.priority<B&&(s?s.close():i.close())}}}}function gt(n,e){return $.defer(function(){e(n)}),{abort:function(){},forceMinPriority:function(){}}}const{Transports:Fn}=A;var Wn=function(n,e,t,r,i,s){var l=Fn[t];if(!l)throw new X(t);var b=(!n.enabledTransports||K(n.enabledTransports,e)!==-1)&&(!n.disabledTransports||K(n.disabledTransports,e)===-1),L;return b?(i=Object.assign({ignoreNullOrigin:n.ignoreNullOrigin},i),L=new $n(e,r,s?s.getAssistant(l):l,i)):L=Xn,L},Xn={isSupported:function(){return!1},connect:function(n,e){var t=$.defer(function(){e(new ee)});return{abort:function(){t.ensureAborted()},forceMinPriority:function(){}}}};function Jn(n){if(n==null)throw"You must pass an options object";if(n.cluster==null)throw"Options object must provide a cluster";"disableStats"in n&&z.warn("The disableStats option is deprecated in favor of enableStats")}const Vn=(n,e)=>{var t="socket_id="+encodeURIComponent(n.socketId);for(var r in e.params)t+="&"+encodeURIComponent(r)+"="+encodeURIComponent(e.params[r]);if(e.paramsProvider!=null){let i=e.paramsProvider();for(var r in i)t+="&"+encodeURIComponent(r)+"="+encodeURIComponent(i[r])}return t};var Gn=n=>{if(typeof A.getAuthorizers()[n.transport]>"u")throw`'${n.transport}' is not a recognized auth transport`;return(e,t)=>{const r=Vn(e,n);A.getAuthorizers()[n.transport](A,r,n,w.UserAuthentication,t)}};const Kn=(n,e)=>{var t="socket_id="+encodeURIComponent(n.socketId);t+="&channel_name="+encodeURIComponent(n.channelName);for(var r in e.params)t+="&"+encodeURIComponent(r)+"="+encodeURIComponent(e.params[r]);if(e.paramsProvider!=null){let i=e.paramsProvider();for(var r in i)t+="&"+encodeURIComponent(r)+"="+encodeURIComponent(i[r])}return t};var Yn=n=>{if(typeof A.getAuthorizers()[n.transport]>"u")throw`'${n.transport}' is not a recognized auth transport`;return(e,t)=>{const r=Kn(e,n);A.getAuthorizers()[n.transport](A,r,n,w.ChannelAuthorization,t)}};const Zn=(n,e,t)=>{const r={authTransport:e.transport,authEndpoint:e.endpoint,auth:{params:e.params,headers:e.headers}};return(i,s)=>{const l=n.channel(i.channelName);t(l,r).authorize(i.socketId,s)}};function Qn(n,e){let t={activityTimeout:n.activityTimeout||p.activityTimeout,cluster:n.cluster,httpPath:n.httpPath||p.httpPath,httpPort:n.httpPort||p.httpPort,httpsPort:n.httpsPort||p.httpsPort,pongTimeout:n.pongTimeout||p.pongTimeout,statsHost:n.statsHost||p.stats_host,unavailableTimeout:n.unavailableTimeout||p.unavailableTimeout,wsPath:n.wsPath||p.wsPath,wsPort:n.wsPort||p.wsPort,wssPort:n.wssPort||p.wssPort,enableStats:ir(n),httpHost:er(n),useTLS:rr(n),wsHost:tr(n),userAuthenticator:sr(n),channelAuthorizer:ar(n,e)};return"disabledTransports"in n&&(t.disabledTransports=n.disabledTransports),"enabledTransports"in n&&(t.enabledTransports=n.enabledTransports),"ignoreNullOrigin"in n&&(t.ignoreNullOrigin=n.ignoreNullOrigin),"timelineParams"in n&&(t.timelineParams=n.timelineParams),"nacl"in n&&(t.nacl=n.nacl),t}function er(n){return n.httpHost?n.httpHost:n.cluster?`sockjs-${n.cluster}.pusher.com`:p.httpHost}function tr(n){return n.wsHost?n.wsHost:nr(n.cluster)}function nr(n){return`ws-${n}.pusher.com`}function rr(n){return A.getProtocol()==="https:"?!0:n.forceTLS!==!1}function ir(n){return"enableStats"in n?n.enableStats:"disableStats"in n?!n.disableStats:!1}function sr(n){const e=Object.assign(Object.assign({},p.userAuthentication),n.userAuthentication);return"customHandler"in e&&e.customHandler!=null?e.customHandler:Gn(e)}function or(n,e){let t;return"channelAuthorization"in n?t=Object.assign(Object.assign({},p.channelAuthorization),n.channelAuthorization):(t={transport:n.authTransport||p.authTransport,endpoint:n.authEndpoint||p.authEndpoint},"auth"in n&&("params"in n.auth&&(t.params=n.auth.params),"headers"in n.auth&&(t.headers=n.auth.headers)),"authorizer"in n&&(t.customHandler=Zn(e,t,n.authorizer))),t}function ar(n,e){const t=or(n,e);return"customHandler"in t&&t.customHandler!=null?t.customHandler:Yn(t)}class cr extends ae{constructor(e){super(function(t,r){z.debug(`No callbacks on watchlist events for ${t}`)}),this.pusher=e,this.bindWatchlistInternalEvent()}handleEvent(e){e.data.events.forEach(t=>{this.emit(t.name,t)})}bindWatchlistInternalEvent(){this.pusher.connection.bind("message",e=>{var t=e.event;t==="pusher_internal:watchlist_events"&&this.handleEvent(e)})}}function lr(){let n,e;return{promise:new Promise((r,i)=>{n=r,e=i}),resolve:n,reject:e}}var ur=lr;class dr extends ae{constructor(e){super(function(t,r){z.debug("No callbacks on user for "+t)}),this.signin_requested=!1,this.user_data=null,this.serverToUserChannel=null,this.signinDonePromise=null,this._signinDoneResolve=null,this._onAuthorize=(t,r)=>{if(t){z.warn(`Error during signin: ${t}`),this._cleanup();return}this.pusher.send_event("pusher:signin",{auth:r.auth,user_data:r.user_data})},this.pusher=e,this.pusher.connection.bind("state_change",({previous:t,current:r})=>{t!=="connected"&&r==="connected"&&this._signin(),t==="connected"&&r!=="connected"&&(this._cleanup(),this._newSigninPromiseIfNeeded())}),this.watchlist=new cr(e),this.pusher.connection.bind("message",t=>{var r=t.event;r==="pusher:signin_success"&&this._onSigninSuccess(t.data),this.serverToUserChannel&&this.serverToUserChannel.name===t.channel&&this.serverToUserChannel.handleEvent(t)})}signin(){this.signin_requested||(this.signin_requested=!0,this._signin())}_signin(){this.signin_requested&&(this._newSigninPromiseIfNeeded(),this.pusher.connection.state==="connected"&&this.pusher.config.userAuthenticator({socketId:this.pusher.connection.socket_id},this._onAuthorize))}_onSigninSuccess(e){try{this.user_data=JSON.parse(e.user_data)}catch{z.error(`Failed parsing user data after signin: ${e.user_data}`),this._cleanup();return}if(typeof this.user_data.id!="string"||this.user_data.id===""){z.error(`user_data doesn't contain an id. user_data: ${this.user_data}`),this._cleanup();return}this._signinDoneResolve(),this._subscribeChannels()}_subscribeChannels(){const e=t=>{t.subscriptionPending&&t.subscriptionCancelled?t.reinstateSubscription():!t.subscriptionPending&&this.pusher.connection.state==="connected"&&t.subscribe()};this.serverToUserChannel=new We(`#server-to-user-${this.user_data.id}`,this.pusher),this.serverToUserChannel.bind_global((t,r)=>{t.indexOf("pusher_internal:")===0||t.indexOf("pusher:")===0||this.emit(t,r)}),e(this.serverToUserChannel)}_cleanup(){this.user_data=null,this.serverToUserChannel&&(this.serverToUserChannel.unbind_all(),this.serverToUserChannel.disconnect(),this.serverToUserChannel=null),this.signin_requested&&this._signinDoneResolve()}_newSigninPromiseIfNeeded(){if(!this.signin_requested||this.signinDonePromise&&!this.signinDonePromise.done)return;const{promise:e,resolve:t}=ur();e.done=!1;const r=()=>{e.done=!0};e.then(r).catch(r),this.signinDonePromise=e,this._signinDoneResolve=t}}class W{static ready(){W.isReady=!0;for(var e=0,t=W.instances.length;e<t;e++)W.instances[e].connect()}static getClientFeatures(){return Pe(o({ws:A.Transports.ws},function(e){return e.isSupported({})}))}constructor(e,t){hr(e),Jn(t),this.key=e,this.config=Qn(t,this),this.channels=ce.createChannels(),this.global_emitter=new ae,this.sessionID=A.randomInt(1e9),this.timeline=new Bn(this.key,this.sessionID,{cluster:this.config.cluster,features:W.getClientFeatures(),params:this.config.timelineParams||{},limit:50,level:Re.INFO,version:p.VERSION}),this.config.enableStats&&(this.timelineSender=ce.createTimelineSender(this.timeline,{host:this.config.statsHost,path:"/timeline/v2/"+A.TimelineTransport.name}));var r=i=>A.getDefaultStrategy(this.config,i,Wn);this.connection=ce.createConnectionManager(this.key,{getStrategy:r,timeline:this.timeline,activityTimeout:this.config.activityTimeout,pongTimeout:this.config.pongTimeout,unavailableTimeout:this.config.unavailableTimeout,useTLS:!!this.config.useTLS}),this.connection.bind("connected",()=>{this.subscribeAll(),this.timelineSender&&this.timelineSender.send(this.connection.isUsingTLS())}),this.connection.bind("message",i=>{var s=i.event,l=s.indexOf("pusher_internal:")===0;if(i.channel){var b=this.channel(i.channel);b&&b.handleEvent(i)}l||this.global_emitter.emit(i.event,i.data)}),this.connection.bind("connecting",()=>{this.channels.disconnect()}),this.connection.bind("disconnected",()=>{this.channels.disconnect()}),this.connection.bind("error",i=>{z.warn(i)}),W.instances.push(this),this.timeline.info({instances:W.instances.length}),this.user=new dr(this),W.isReady&&this.connect()}channel(e){return this.channels.find(e)}allChannels(){return this.channels.all()}connect(){if(this.connection.connect(),this.timelineSender&&!this.timelineSenderTimer){var e=this.connection.isUsingTLS(),t=this.timelineSender;this.timelineSenderTimer=new Ee(6e4,function(){t.send(e)})}}disconnect(){this.connection.disconnect(),this.timelineSenderTimer&&(this.timelineSenderTimer.ensureAborted(),this.timelineSenderTimer=null)}bind(e,t,r){return this.global_emitter.bind(e,t,r),this}unbind(e,t,r){return this.global_emitter.unbind(e,t,r),this}bind_global(e){return this.global_emitter.bind_global(e),this}unbind_global(e){return this.global_emitter.unbind_global(e),this}unbind_all(e){return this.global_emitter.unbind_all(),this}subscribeAll(){var e;for(e in this.channels.channels)this.channels.channels.hasOwnProperty(e)&&this.subscribe(e)}subscribe(e){var t=this.channels.add(e,this);return t.subscriptionPending&&t.subscriptionCancelled?t.reinstateSubscription():!t.subscriptionPending&&this.connection.state==="connected"&&t.subscribe(),t}unsubscribe(e){var t=this.channels.find(e);t&&t.subscriptionPending?t.cancelSubscription():(t=this.channels.remove(e),t&&t.subscribed&&t.unsubscribe())}send_event(e,t,r){return this.connection.send_event(e,t,r)}shouldUseTLS(){return this.config.useTLS}signin(){this.user.signin()}}W.instances=[],W.isReady=!1,W.logToConsole=!1,W.Runtime=A,W.ScriptReceivers=A.ScriptReceivers,W.DependenciesReceivers=A.DependenciesReceivers,W.auth_callbacks=A.auth_callbacks;var Ze=I.default=W;function hr(n){if(n==null)throw"You must pass your app key when you instantiate Pusher."}A.setup(W)})])})})(ge)),ge.exports}var Tt=Ct();const kt=Te(Tt),be={MessageSent:"App\\Events\\MessageSent",MessageRead:"App\\Events\\MessageRead",TypingStarted:"App\\Events\\TypingStarted",AdminStatusChanged:"App\\Events\\AdminStatusChanged",MessageTranslated:"App\\Events\\MessageTranslated"};function Lt(N){const{apiUrl:H,wsKey:v,wsPort:I,forceTLS:C=!1}=N,x=new URL(H),c=x.hostname;let y=null,p=null,P=null,f=!1;const _={};function d(T,m){_[T]||(_[T]=[]),_[T].push(m)}function g(T,m){_[T]&&_[T].forEach(M=>M(m))}function S(){return f}function w(){y||(y=new kt(v,{wsHost:c,wsPort:I||(C?443:parseInt(x.port)||80),wssPort:I||443,forceTLS:C,enabledTransports:["ws"],disableStats:!0,cluster:"mt1"}),y.connection.bind("connected",()=>{f=!0,g("connected")}),y.connection.bind("disconnected",()=>{f=!1,g("disconnected")}),y.connection.bind("error",T=>{f=!1,g("error",T)}))}function u(T){y||w(),p&&p.unbind_all(),p=y.subscribe(`conversation.${T}`),p.bind(be.MessageSent,m=>g("message",m.message)),p.bind(be.MessageRead,m=>g("read",m)),p.bind(be.TypingStarted,m=>g("typing",m)),p.bind(be.MessageTranslated,m=>g("translated",m)),p.bind("MessageSent",m=>g("message",m.message)),p.bind("MessageRead",m=>g("read",m)),p.bind("TypingStarted",m=>g("typing",m)),p.bind("MessageTranslated",m=>g("translated",m))}function h(T){y||w(),P&&P.unbind_all(),P=y.subscribe(`admin.site.${T}`),P.bind(be.AdminStatusChanged,m=>g("adminStatus",m)),P.bind("AdminStatusChanged",m=>g("adminStatus",m))}function k(){p&&(p.unbind_all(),p=null),P&&(P.unbind_all(),P=null),y&&(y.disconnect(),y=null),f=!1}return{on:d,connect:w,subscribeConversation:u,subscribeSite:h,disconnect:k,isConnected:S}}const ye={en:{title:"Live Chat",placeholder:"Type a message...",send:"Send",online:"Customer Service",offline:"Offline",enterName:"Enter your name",startChat:"Start Chat",noMessages:"No messages yet",rateLimitCooldown:"Please wait a few seconds before sending another message.",rateLimitExceeded:"You have sent too many messages. Please try again later.",messageTooLong:"Message is too long. Maximum 1000 characters.",aiLabel:"AI",aiActive:"AI Assistant",aiTyping:"AI is thinking...",connectionError:"Connection lost. Reconnecting...",serviceUnavailable:"Chat service is currently unavailable. Please try again later.",translatedFrom:"Translated from",showOriginal:"Show original",greeting:"Hi! How can I help you today?",conversationClosed:"This conversation has been closed.",startNewChat:"Start New Chat"},nl:{title:"Live Chat",placeholder:"Typ een bericht...",send:"Verzenden",online:"Klantenservice",offline:"Offline",enterName:"Voer uw naam in",startChat:"Start Chat",noMessages:"Nog geen berichten",rateLimitCooldown:"Wacht een paar seconden voordat u nog een bericht stuurt.",rateLimitExceeded:"U heeft te veel berichten gestuurd. Probeer het later opnieuw.",messageTooLong:"Bericht is te lang. Maximaal 1000 tekens.",aiLabel:"AI",aiActive:"AI-assistent",aiTyping:"AI denkt na...",connectionError:"Verbinding verbroken. Opnieuw verbinden...",serviceUnavailable:"Chatservice is momenteel niet beschikbaar. Probeer het later opnieuw.",translatedFrom:"Vertaald uit",showOriginal:"Toon origineel",greeting:"Hallo! Hoe kan ik u vandaag helpen?",conversationClosed:"Dit gesprek is gesloten.",startNewChat:"Nieuw Gesprek"},de:{title:"Live-Chat",placeholder:"Nachricht eingeben...",send:"Senden",online:"Kundenservice",offline:"Offline",enterName:"Geben Sie Ihren Namen ein",startChat:"Chat starten",noMessages:"Noch keine Nachrichten",rateLimitCooldown:"Bitte warten Sie einige Sekunden, bevor Sie eine weitere Nachricht senden.",rateLimitExceeded:"Sie haben zu viele Nachrichten gesendet. Bitte versuchen Sie es später erneut.",messageTooLong:"Nachricht ist zu lang. Maximal 1000 Zeichen.",aiLabel:"KI",aiActive:"KI-Assistent",aiTyping:"KI denkt nach...",connectionError:"Verbindung verloren. Erneut verbinden...",serviceUnavailable:"Chat-Service ist derzeit nicht verfügbar. Bitte versuchen Sie es später erneut.",translatedFrom:"Übersetzt aus",showOriginal:"Original anzeigen",greeting:"Hallo! Wie kann ich Ihnen heute helfen?"},fr:{title:"Chat en direct",placeholder:"Tapez un message...",send:"Envoyer",online:"Service client",offline:"Hors ligne",enterName:"Entrez votre nom",startChat:"Démarrer le chat",noMessages:"Pas encore de messages",rateLimitCooldown:"Veuillez patienter quelques secondes avant d'envoyer un autre message.",rateLimitExceeded:"Vous avez envoyé trop de messages. Veuillez réessayer plus tard.",messageTooLong:"Le message est trop long. Maximum 1000 caractères.",aiLabel:"IA",aiActive:"Assistant IA",aiTyping:"L'IA réfléchit...",connectionError:"Connexion perdue. Reconnexion...",serviceUnavailable:"Le service de chat est actuellement indisponible. Veuillez réessayer plus tard.",translatedFrom:"Traduit de",showOriginal:"Afficher l'original",greeting:"Bonjour ! Comment puis-je vous aider aujourd'hui ?"},es:{title:"Chat en vivo",placeholder:"Escribe un mensaje...",send:"Enviar",online:"Atencion al cliente",offline:"Desconectado",enterName:"Ingresa tu nombre",startChat:"Iniciar chat",noMessages:"Aún no hay mensajes",rateLimitCooldown:"Por favor espera unos segundos antes de enviar otro mensaje.",rateLimitExceeded:"Has enviado demasiados mensajes. Por favor inténtalo más tarde.",messageTooLong:"El mensaje es demasiado largo. Máximo 1000 caracteres.",aiLabel:"IA",aiActive:"Asistente IA",aiTyping:"La IA está pensando...",connectionError:"Conexión perdida. Reconectando...",serviceUnavailable:"El servicio de chat no está disponible actualmente. Por favor inténtalo más tarde.",translatedFrom:"Traducido de",showOriginal:"Mostrar original",greeting:"¡Hola! ¿Cómo puedo ayudarte hoy?"},pt:{title:"Chat ao vivo",placeholder:"Digite uma mensagem...",send:"Enviar",online:"Atendimento ao cliente",offline:"Offline",enterName:"Digite seu nome",startChat:"Iniciar chat",noMessages:"Nenhuma mensagem ainda",rateLimitCooldown:"Por favor aguarde alguns segundos antes de enviar outra mensagem.",rateLimitExceeded:"Você enviou muitas mensagens. Por favor tente novamente mais tarde.",messageTooLong:"Mensagem muito longa. Máximo de 1000 caracteres.",aiLabel:"IA",aiActive:"Assistente IA",aiTyping:"IA está pensando...",connectionError:"Conexão perdida. Reconectando...",serviceUnavailable:"O serviço de chat está indisponível no momento. Por favor tente novamente mais tarde.",translatedFrom:"Traduzido de",showOriginal:"Mostrar original",greeting:"Olá! Como posso ajudá-lo hoje?"},tr:{title:"Canlı Sohbet",placeholder:"Bir mesaj yazın...",send:"Gönder",online:"Musteri Hizmetleri",offline:"Çevrimdışı",enterName:"Adınızı girin",startChat:"Sohbeti Başlat",noMessages:"Henüz mesaj yok",rateLimitCooldown:"Lütfen başka bir mesaj göndermeden önce birkaç saniye bekleyin.",rateLimitExceeded:"Çok fazla mesaj gönderdiniz. Lütfen daha sonra tekrar deneyin.",messageTooLong:"Mesaj çok uzun. Maksimum 1000 karakter.",aiLabel:"YZ",aiActive:"YZ Asistanı",aiTyping:"YZ düşünüyor...",connectionError:"Bağlantı kesildi. Yeniden bağlanılıyor...",serviceUnavailable:"Sohbet hizmeti şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyin.",translatedFrom:"Şu dilden çevrildi:",showOriginal:"Orijinali göster",greeting:"Merhaba! Bugün size nasıl yardımcı olabilirim?",conversationClosed:"Bu görüşme kapatılmıştır.",startNewChat:"Yeni Sohbet"},zh:{title:"在线聊天",placeholder:"输入消息...",send:"发送",online:"客户服务",offline:"离线",enterName:"请输入您的姓名",startChat:"开始聊天",noMessages:"暂无消息",rateLimitCooldown:"请等待几秒钟再发送下一条消息。",rateLimitExceeded:"您发送的消息过多，请稍后再试。",messageTooLong:"消息过长，最多1000个字符。",aiLabel:"AI",aiActive:"AI助手",aiTyping:"AI正在思考...",connectionError:"连接断开，正在重新连接...",serviceUnavailable:"聊天服务暂时不可用，请稍后再试。",translatedFrom:"翻译自",showOriginal:"显示原文",greeting:"您好！今天我能为您做什么？"},ja:{title:"ライブチャット",placeholder:"メッセージを入力...",send:"送信",online:"カスタマーサービス",offline:"オフライン",enterName:"お名前を入力してください",startChat:"チャットを開始",noMessages:"メッセージはまだありません",rateLimitCooldown:"次のメッセージを送信する前に数秒お待ちください。",rateLimitExceeded:"メッセージの送信が多すぎます。後でもう一度お試しください。",messageTooLong:"メッセージが長すぎます。最大1000文字です。",aiLabel:"AI",aiActive:"AIアシスタント",aiTyping:"AIが考えています...",connectionError:"接続が切断されました。再接続中...",serviceUnavailable:"チャットサービスは現在利用できません。後でもう一度お試しください。",translatedFrom:"翻訳元：",showOriginal:"原文を表示",greeting:"こんにちは！本日はどのようにお手伝いできますか？"},ko:{title:"실시간 채팅",placeholder:"메시지를 입력하세요...",send:"전송",online:"고객센터",offline:"오프라인",enterName:"이름을 입력하세요",startChat:"채팅 시작",noMessages:"아직 메시지가 없습니다",rateLimitCooldown:"다음 메시지를 보내기 전에 잠시 기다려 주세요.",rateLimitExceeded:"너무 많은 메시지를 보냈습니다. 나중에 다시 시도해 주세요.",messageTooLong:"메시지가 너무 깁니다. 최대 1000자입니다.",aiLabel:"AI",aiActive:"AI 어시스턴트",aiTyping:"AI가 생각 중...",connectionError:"연결이 끊어졌습니다. 재연결 중...",serviceUnavailable:"채팅 서비스를 현재 이용할 수 없습니다. 나중에 다시 시도해 주세요.",translatedFrom:"번역 원본:",showOriginal:"원문 보기",greeting:"안녕하세요! 오늘 무엇을 도와드릴까요?"},ar:{title:"الدردشة المباشرة",placeholder:"اكتب رسالة...",send:"إرسال",online:"خدمة العملاء",offline:"غير متصل",enterName:"أدخل اسمك",startChat:"بدء الدردشة",noMessages:"لا توجد رسائل بعد",rateLimitCooldown:"يرجى الانتظار بضع ثوانٍ قبل إرسال رسالة أخرى.",rateLimitExceeded:"لقد أرسلت رسائل كثيرة جداً. يرجى المحاولة لاحقاً.",messageTooLong:"الرسالة طويلة جداً. الحد الأقصى 1000 حرف.",aiLabel:"ذ.ا.",aiActive:"مساعد الذكاء الاصطناعي",aiTyping:"الذكاء الاصطناعي يفكر...",connectionError:"انقطع الاتصال. جارٍ إعادة الاتصال...",serviceUnavailable:"خدمة الدردشة غير متاحة حالياً. يرجى المحاولة لاحقاً.",translatedFrom:"مترجم من",showOriginal:"عرض الأصل",greeting:"مرحباً! كيف يمكنني مساعدتك اليوم؟"},ru:{title:"Онлайн-чат",placeholder:"Введите сообщение...",send:"Отправить",online:"Служба поддержки",offline:"Офлайн",enterName:"Введите ваше имя",startChat:"Начать чат",noMessages:"Сообщений пока нет",rateLimitCooldown:"Пожалуйста, подождите несколько секунд перед отправкой следующего сообщения.",rateLimitExceeded:"Вы отправили слишком много сообщений. Пожалуйста, попробуйте позже.",messageTooLong:"Сообщение слишком длинное. Максимум 1000 символов.",aiLabel:"ИИ",aiActive:"ИИ-ассистент",aiTyping:"ИИ думает...",connectionError:"Соединение потеряно. Переподключение...",serviceUnavailable:"Чат-сервис временно недоступен. Пожалуйста, попробуйте позже.",translatedFrom:"Переведено с",showOriginal:"Показать оригинал",greeting:"Здравствуйте! Чем могу помочь?"},hi:{title:"लाइव चैट",placeholder:"संदेश लिखें...",send:"भेजें",online:"ग्राहक सेवा",offline:"ऑफलाइन",enterName:"अपना नाम दर्ज करें",startChat:"चैट शुरू करें",noMessages:"अभी तक कोई संदेश नहीं",rateLimitCooldown:"कृपया अगला संदेश भेजने से पहले कुछ सेकंड प्रतीक्षा करें।",rateLimitExceeded:"आपने बहुत अधिक संदेश भेजे हैं। कृपया बाद में पुनः प्रयास करें।",messageTooLong:"संदेश बहुत लंबा है। अधिकतम 1000 अक्षर।",aiLabel:"AI",aiActive:"AI सहायक",aiTyping:"AI सोच रहा है...",connectionError:"कनेक्शन टूट गया। पुनः कनेक्ट हो रहा है...",serviceUnavailable:"चैट सेवा वर्तमान में अनुपलब्ध है। कृपया बाद में पुनः प्रयास करें।",translatedFrom:"अनुवादित भाषा:",showOriginal:"मूल दिखाएं",greeting:"नमस्ते! आज मैं आपकी कैसे मदद कर सकता हूँ?"}};function At(N="en"){let H=ye[N]?N:"en";return{t(v){var I;return((I=ye[H])==null?void 0:I[v])||ye.en[v]||v},setLanguage(v){ye[v]&&(H=v)},getLanguage(){return H},getSupportedLanguages(){return Object.keys(ye)}}}const Et='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',nt='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';function Pt({api:N,ws:H,i18n:v,position:I="bottom-right"}){let C=!1,x=!1,c=[],y=0,p=!1,P=!1,f=null,_="",d="active",g="";const S=new Set,w="chatpilot_language",u=v.getSupportedLanguages?v.getSupportedLanguages():["en","tr","nl"];function h(){try{const o=localStorage.getItem(w);return u.includes(o)?o:v.getLanguage()}catch{return v.getLanguage()}}function k(o){if(u.includes(o)){v.setLanguage(o);try{localStorage.setItem(w,o)}catch{}}}k(h());let T,m,M,X=null;function ee(o){return new Date(o).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}function V(){T=document.createElement("chatpilot-widget"),m=T.attachShadow({mode:"open"});const o=document.createElement("style");o.textContent=`:host {
  --cp-primary: #6366f1;
  --cp-primary-dark: #4f46e5;
  --cp-bg: #ffffff;
  --cp-bg-alt: #f9fafb;
  --cp-text: #111827;
  --cp-text-secondary: #6b7280;
  --cp-border: #e5e7eb;
  --cp-error: #dc2626;
  --cp-error-bg: #fef2f2;
  --cp-error-border: #fecaca;
  --cp-ai-color: #7c3aed;
  --cp-ai-bg: #ede9fe;
  --cp-success: #22c55e;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 14px;
  line-height: 1.4;
}

:host(.dark) {
  --cp-primary: #818cf8;
  --cp-primary-dark: #6366f1;
  --cp-bg: #1f2937;
  --cp-bg-alt: #374151;
  --cp-text: #f9fafb;
  --cp-text-secondary: #9ca3af;
  --cp-border: #4b5563;
  --cp-error: #fca5a5;
  --cp-error-bg: #450a0a;
  --cp-error-border: #7f1d1d;
  --cp-ai-color: #c4b5fd;
  --cp-ai-bg: #2e1065;
  --cp-success: #4ade80;
}

*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.cp-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 2147483647;
}

.cp-container.left {
  right: auto;
  left: 24px;
}

/* Floating Button */
.cp-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--cp-primary);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s, background-color 0.2s;
  position: relative;
}

.cp-button:hover {
  transform: scale(1.05);
  background: var(--cp-primary-dark);
}

.cp-button.open {
  background: var(--cp-text-secondary);
}

.cp-button svg {
  width: 28px;
  height: 28px;
}

.cp-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 12px;
  font-weight: 600;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 6px;
}

/* Chat Window */
.cp-window {
  position: absolute;
  bottom: 72px;
  right: 0;
  width: 360px;
  max-width: calc(100vw - 48px);
  height: 480px;
  max-height: calc(100vh - 120px);
  background: var(--cp-bg);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--cp-border);
  animation: cp-slideUp 0.2s ease-out;
}

.cp-container.left .cp-window {
  right: auto;
  left: 0;
}

@keyframes cp-slideUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Header */
.cp-header {
  padding: 16px;
  background: var(--cp-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.cp-header-info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.cp-status {
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  opacity: 0.9;
}

.cp-status::before {
  content: '';
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9ca3af;
}

.cp-status.online::before { background: var(--cp-success); }
.cp-status.ai::before { background: var(--cp-success); }

.cp-close {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
  padding: 4px;
  display: flex;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.cp-close:hover { opacity: 1; }
.cp-close svg { width: 20px; height: 20px; }

.cp-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cp-lang-select {
  background: rgba(255, 255, 255, 0.18);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 6px;
  font-size: 11px;
  padding: 4px 6px;
  min-width: 56px;
  cursor: pointer;
}

.cp-lang-select option {
  color: #111827;
}

/* Name Form */
.cp-name-form {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  text-align: center;
}

.cp-name-form p {
  color: var(--cp-text);
  margin-bottom: 16px;
}

.cp-name-form form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cp-name-form input {
  padding: 12px 16px;
  border: 1px solid var(--cp-border);
  border-radius: 8px;
  font-size: 14px;
  background: var(--cp-bg-alt);
  color: var(--cp-text);
  outline: none;
  transition: border-color 0.2s;
}

.cp-name-form input:focus { border-color: var(--cp-primary); }

.cp-name-form button {
  padding: 12px 24px;
  background: var(--cp-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cp-name-form button:hover:not(:disabled) { background: var(--cp-primary-dark); }
.cp-name-form button:disabled { opacity: 0.5; cursor: not-allowed; }

/* Messages */
.cp-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cp-no-messages {
  color: var(--cp-text-secondary);
  text-align: center;
  margin: auto;
}

.cp-msg {
  display: flex;
  max-width: 85%;
}

.cp-msg.sent { align-self: flex-end; }
.cp-msg.received { align-self: flex-start; }

.cp-msg-content {
  padding: 10px 14px;
  border-radius: 16px;
}

.cp-msg.sent .cp-msg-content {
  background: var(--cp-primary);
  color: white;
  border-bottom-right-radius: 4px;
}

.cp-msg.received .cp-msg-content {
  background: var(--cp-bg-alt);
  color: var(--cp-text);
  border: 1px solid var(--cp-border);
  border-bottom-left-radius: 4px;
}

.cp-msg-content p {
  margin: 0;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.cp-msg-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.cp-msg-time {
  font-size: 10px;
  opacity: 0.7;
}

.cp-msg.sent .cp-msg-meta { justify-content: flex-end; }

/* AI badge */
.cp-ai-badge {
  font-size: 10px;
  font-weight: 600;
  color: var(--cp-ai-color);
  background: var(--cp-ai-bg);
  padding: 2px 6px;
  border-radius: 4px;
  margin-bottom: 4px;
  display: inline-block;
}

.cp-msg.ai .cp-msg-content {
  border-color: #c4b5fd;
}

/* System Messages */
.cp-msg.system {
  display: flex;
  justify-content: center;
  margin: 8px 0;
}

.cp-msg.system .cp-msg-content {
  background: var(--cp-bg-alt);
  border: 1px dashed var(--cp-border);
  border-radius: 12px;
  padding: 6px 14px;
  max-width: none;
}

.cp-msg.system .cp-msg-content p {
  color: var(--cp-text-secondary);
  font-size: 12px;
  text-align: center;
  font-style: italic;
}

/* Closed Conversation */
.cp-closed-area {
  text-align: center;
  padding: 16px !important;
}

.cp-closed-msg {
  color: var(--cp-text-secondary);
  font-size: 13px;
  margin-bottom: 10px;
}

.cp-new-chat-btn {
  background: var(--cp-primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
}

.cp-new-chat-btn:hover {
  background: var(--cp-primary-dark);
}

/* Offline Status */
.cp-status.offline::before { background: var(--cp-text-secondary); }

/* Translate Toggle */
.cp-translate-toggle {
  background: none;
  border: none;
  color: var(--cp-text-secondary);
  font-size: 10px;
  cursor: pointer;
  padding: 2px 0;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.cp-translate-toggle:hover { opacity: 1; }

.cp-msg.sent .cp-translate-toggle { color: rgba(255, 255, 255, 0.7); }
.cp-msg.sent .cp-translate-toggle:hover { color: rgba(255, 255, 255, 0.9); }

/* Typing Indicator */
.cp-typing {
  display: flex;
  gap: 4px;
  padding: 4px 0;
}

.cp-typing span {
  width: 8px;
  height: 8px;
  background: var(--cp-ai-color);
  border-radius: 50%;
  animation: cp-bounce 1.4s infinite ease-in-out both;
}

.cp-typing span:nth-child(1) { animation-delay: -0.32s; }
.cp-typing span:nth-child(2) { animation-delay: -0.16s; }

@keyframes cp-bounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}

/* Input */
.cp-input-area {
  border-top: 1px solid var(--cp-border);
  background: var(--cp-bg);
  flex-shrink: 0;
}

.cp-input-form {
  padding: 12px 16px;
  display: flex;
  gap: 8px;
}

.cp-input-form input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid var(--cp-border);
  border-radius: 20px;
  font-size: 14px;
  background: var(--cp-bg-alt);
  color: var(--cp-text);
  outline: none;
  transition: border-color 0.2s;
}

.cp-input-form input:focus { border-color: var(--cp-primary); }

.cp-input-form button {
  padding: 10px 18px;
  background: var(--cp-primary);
  color: white;
  border: none;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cp-input-form button:hover:not(:disabled) { background: var(--cp-primary-dark); }
.cp-input-form button:disabled { opacity: 0.5; cursor: not-allowed; }

/* Character counter */
.cp-char-counter {
  font-size: 11px;
  color: var(--cp-text-secondary);
  text-align: right;
  padding: 2px 16px 4px;
}

.cp-char-counter.warning { color: var(--cp-error); }

/* Rate limit error */
.cp-error {
  padding: 6px 12px;
  background: var(--cp-error-bg);
  color: var(--cp-error);
  font-size: 12px;
  text-align: center;
  border-top: 1px solid var(--cp-error-border);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.cp-error-dismiss {
  background: none;
  border: none;
  color: var(--cp-error);
  font-size: 16px;
  cursor: pointer;
  padding: 0 2px;
  line-height: 1;
  opacity: 0.7;
}

.cp-error-dismiss:hover { opacity: 1; }

/* Responsive */
@media (max-width: 480px) {
  .cp-container { bottom: 16px; right: 16px; }
  .cp-container.left { left: 16px; }
  .cp-button { width: 54px; height: 54px; }
  .cp-window {
    width: calc(100vw - 32px);
    height: calc(100vh - 100px);
    bottom: 66px;
  }
}
`,m.appendChild(o),M=document.createElement("div"),M.className=`cp-container${I==="bottom-left"?" left":""}`,m.appendChild(M),document.body.appendChild(T);const a=N.getSession();a!=null&&a.conversationId&&(x=!0,ke(a.conversationId),Le(a.conversationId),Z(a.conversationId)),K()}function Z(o){G(),X=setInterval(async()=>{H.isConnected()||await ue(o)},3e3)}function G(){X&&(clearInterval(X),X=null)}async function ke(o){try{const a=await N.getMessages(o,null,v.getLanguage());if(c=(a.messages||a.data||a||[]).map(se),a.conversation_status){const E=d==="active";d=a.conversation_status,E&&d==="closed"&&G()}Me(),K(),te()}catch(a){(a.status===401||a.status===403||a.status===404)&&(N.clearSession(),x=!1,c=[],K())}}async function ue(o){try{const a=await N.getMessages(o,null,v.getLanguage());if(a.conversation_status&&a.conversation_status!==d){d=a.conversation_status,d==="closed"&&G(),c=(a.messages||a.data||a||[]).map(se),K(),te();return}const E=(a.messages||a.data||a||[]).map(se);if(E.length!==c.length){const U=new Set(c.map(j=>j.id)),D=E.filter(j=>!U.has(j.id));D.length>0&&(D.forEach(j=>{c.push(j),!C&&j.sender!=="visitor"&&y++}),P=!1,oe(),$(),te())}}catch{}}function se(o){return{id:o.id,text:o.text,sender:o.sender_type||o.sender,timestamp:o.created_at||o.timestamp,readAt:o.read_at,language:o.language||null,translations:o.translations||null}}function Le(o){H.subscribeConversation(o),H.on("message",a=>{const E=se(a);c.find(U=>U.id===E.id)||(c=c.filter(U=>!(U.id.startsWith("temp-")&&U.text===E.text)),c.push(E),!C&&E.sender!=="visitor"&&y++,P=!1,oe(),$(),te())}),H.on("typing",a=>{a.sender_type!=="visitor"&&(P=!0,oe(),te())}),H.on("read",()=>{c.forEach(a=>{a.sender==="visitor"&&(a.readAt=new Date().toISOString())})}),H.on("translated",a=>{const E=c.find(U=>U.id===a.message_id);E&&(E.translations=a.translations,oe())}),H.on("adminStatus",a=>{p=a.online;const E=m.querySelector(".cp-status");E&&(E.className=`cp-status ${p?"online":"ai"}`,E.textContent=p?v.t("online"):v.t("aiActive"))})}function Me(){C?y=0:y=c.filter(o=>o.sender!=="visitor"&&!o.readAt).length}function te(){requestAnimationFrame(()=>{const o=m.querySelector(".cp-messages");o&&(o.scrollTop=o.scrollHeight)})}function me(){C=!C,C&&(y=0),K(),C&&te()}async function Ue(o){var D;o.preventDefault();const a=m.querySelector(".cp-name-input"),E=(D=a==null?void 0:a.value)==null?void 0:D.trim();if(!E)return;const U=m.querySelector(".cp-name-btn");U.disabled=!0;try{const j=await N.createConversation(E,{page_url:window.location.href,language:v.getLanguage()});x=!0,Le(j.conversationId),Z(j.conversationId),K()}catch(j){U.disabled=!1,f=j.code==="SERVICE_UNAVAILABLE"?v.t("serviceUnavailable"):j.message||v.t("serviceUnavailable");let F=m.querySelector(".cp-name-form .cp-error");if(!F){F=document.createElement("div"),F.className="cp-error";const Q=m.querySelector(".cp-name-form");Q&&Q.appendChild(F)}F.innerHTML=`<span>${f}</span>`,f=null}}async function Ae(o){var j;o.preventDefault();const a=m.querySelector(".cp-msg-input"),E=(j=a==null?void 0:a.value)==null?void 0:j.trim();if(!E)return;if(E.length>1e3){f=v.t("messageTooLong"),q();return}const U=N.getSession();if(!(U!=null&&U.conversationId))return;a.value="",_="",f=null,we();const D={id:"temp-"+Date.now(),text:E,sender:"visitor",timestamp:new Date().toISOString()};c.push(D),oe(),te();try{await N.sendMessage(U.conversationId,E,v.getLanguage())}catch(F){c=c.filter(Q=>Q.id!==D.id),F.code==="UNAUTHORIZED"?(N.clearSession(),x=!1,c=[],d="active",f=v.t("conversationClosed"),q(),K()):F.code==="RATE_LIMITED"?(f=v.t("rateLimitCooldown"),q(),setTimeout(()=>{f===v.t("rateLimitCooldown")&&(f=null,we())},(F.retryAfter||3)*1e3)):F.code==="SERVICE_UNAVAILABLE"?(f=v.t("serviceUnavailable"),q()):(f=F.message||v.t("serviceUnavailable"),q()),oe()}}function je(o){_=o.target.value;const a=m.querySelector(".cp-char-counter");a&&(_.length>0?(a.textContent=`${_.length}/1000`,a.style.display="block",a.className=`cp-char-counter${_.length>900?" warning":""}`):a.style.display="none")}function ze(){G(),H.disconnect(),N.clearSession(),x=!1,c=[],d="active",K()}function ie(){f=null,we()}function Ee(o){if(o.sender==="system")return`<div class="cp-msg system"><div class="cp-msg-content"><p>${Se(o.text)}</p></div></div>`;const a=o.sender==="visitor",E=o.sender==="ai",U=v.getLanguage(),D=!a&&o.translations&&o.translations[U],j=S.has(o.id),F=D&&!j?o.translations[U]:o.text;let Y=`<div class="${`cp-msg ${a?"sent":"received"}${E?" ai":""}`}"><div class="cp-msg-content">`;if(E&&(Y+=`<span class="cp-ai-badge">${v.t("aiLabel")}</span>`),Y+=`<p>${Se(F)}</p>`,D){const z=(o.language||"en").toUpperCase(),De=j?v.t("translatedFrom"):v.t("showOriginal");Y+=`<button class="cp-translate-toggle" data-msg-id="${o.id}">${De}${j?"":` (${z})`}</button>`}return Y+=`<div class="cp-msg-meta"><span class="cp-msg-time">${ee(o.timestamp)}</span></div>`,Y+="</div></div>",Y}function oe(){const o=m.querySelector(".cp-messages");if(!o)return;let a="";c.length===0?a=`<div class="cp-no-messages">${v.t("noMessages")}</div>`:c.forEach(E=>{a+=Ee(E)}),P&&(a+=`<div class="cp-msg received ai"><div class="cp-msg-content">
        <span class="cp-ai-badge">${v.t("aiLabel")}</span>
        <div class="cp-typing"><span></span><span></span><span></span></div>
      </div></div>`),o.innerHTML=a,xe()}function $(){const o=m.querySelector(".cp-badge"),a=m.querySelector(".cp-button");if(a)if(!C&&y>0)if(o)o.textContent=y;else{const E=document.createElement("span");E.className="cp-badge",E.textContent=y,a.appendChild(E)}else o&&o.remove()}function q(){const o=m.querySelector(".cp-input-area");if(!o||!f)return;let a=m.querySelector(".cp-error");a||(a=document.createElement("div"),a.className="cp-error",o.insertBefore(a,o.firstChild)),a.innerHTML=`<span>${f}</span><button class="cp-error-dismiss">&times;</button>`,a.querySelector(".cp-error-dismiss").addEventListener("click",ie)}function we(){const o=m.querySelector(".cp-error");o&&o.remove()}function K(){if(M.innerHTML="",C){const a=document.createElement("div");a.className="cp-window",a.innerHTML=ne()+(x?He()+de():Pe()),M.appendChild(a),requestAnimationFrame(()=>qe())}const o=document.createElement("button");o.className=`cp-button${C?" open":""}`,o.innerHTML=C?nt:Et,!C&&y>0&&(o.innerHTML+=`<span class="cp-badge">${y}</span>`),o.addEventListener("click",me),M.appendChild(o)}function ne(){let o,a;d==="closed"?(o="offline",a=v.t("conversationClosed")):p?(o="online",a=v.t("online")):(o="ai",a=v.t("aiActive"));const E=v.getLanguage(),U=u.map(D=>`<option value="${D}" ${E===D?"selected":""}>${D.toUpperCase()}</option>`).join("");return`<div class="cp-header">
      <div class="cp-header-info">
        <h3>${v.t("title")}</h3>
        <div class="cp-status ${o}">${a}</div>
      </div>
      <div class="cp-header-actions">
        <select class="cp-lang-select" aria-label="Language">${U}</select>
        <button class="cp-close">${nt}</button>
      </div>
    </div>`}function Pe(){return`<div class="cp-name-form">
      <p>${g||v.t("enterName")}</p>
      <form>
        <input type="text" class="cp-name-input" placeholder="${v.t("enterName")}" maxlength="50" required>
        <button type="submit" class="cp-name-btn">${v.t("startChat")}</button>
      </form>
    </div>`}function He(){let o='<div class="cp-messages">';return c.length===0?o+=`<div class="cp-no-messages">${v.t("noMessages")}</div>`:c.forEach(a=>{o+=Ee(a)}),P&&(o+=`<div class="cp-msg received ai"><div class="cp-msg-content">
        <span class="cp-ai-badge">${v.t("aiLabel")}</span>
        <div class="cp-typing"><span></span><span></span><span></span></div>
      </div></div>`),o+="</div>",o}function de(){if(d==="closed")return`<div class="cp-input-area cp-closed-area">
        <p class="cp-closed-msg">${v.t("conversationClosed")}</p>
        <button class="cp-new-chat-btn">${v.t("startNewChat")}</button>
      </div>`;let o='<div class="cp-input-area">';return f&&(o+=`<div class="cp-error"><span>${f}</span><button class="cp-error-dismiss">&times;</button></div>`),o+=`<div class="cp-char-counter" style="display:none"></div>
      <form class="cp-input-form">
        <input type="text" class="cp-msg-input" placeholder="${v.t("placeholder")}" maxlength="1000">
        <button type="submit">${v.t("send")}</button>
      </form></div>`,o}function xe(){m.querySelectorAll(".cp-translate-toggle").forEach(o=>{o.addEventListener("click",()=>{const a=o.dataset.msgId;S.has(a)?S.delete(a):S.add(a),oe(),te()})})}function qe(){const o=m.querySelector(".cp-close");o&&o.addEventListener("click",me);const a=m.querySelector(".cp-lang-select");a&&a.addEventListener("change",Q=>{k(Q.target.value);const Y=N.getSession();Y!=null&&Y.conversationId&&ke(Y.conversationId),K()});const E=m.querySelector(".cp-new-chat-btn");E&&E.addEventListener("click",ze);const U=m.querySelector(".cp-name-form form");U&&U.addEventListener("submit",Ue);const D=m.querySelector(".cp-input-form");D&&D.addEventListener("submit",Ae);const j=m.querySelector(".cp-msg-input");j&&(j.addEventListener("input",je),j.focus());const F=m.querySelector(".cp-error-dismiss");F&&F.addEventListener("click",ie),xe(),te()}function Se(o){const a=document.createElement("div");return a.textContent=o,a.innerHTML}return{init:V,open(){C||me()},close(){C&&me()},setAdminOnline(o){p=!!o,C&&K()},destroy(){G(),H.disconnect(),T&&T.parentNode&&T.parentNode.removeChild(T)},setTheme(o){o==="dark"?T.classList.add("dark"):T.classList.remove("dark")},applySettings(o){if(!(o!=null&&o.widget))return;const a=o.widget;a.theme&&(a.theme==="dark"?T.classList.add("dark"):T.classList.remove("dark")),a.position&&(a.position==="bottom-left"?M.classList.add("left"):M.classList.remove("left")),a.greeting&&(g=a.greeting)}}}let re=null;const rt={init(N={}){if(re)return console.warn("ChatPilot: Widget already initialized"),re;const{siteKey:H,apiUrl:v,wsKey:I,wsPort:C,language:x,position:c,theme:y,forceTLS:p}=N;if(!H||!v)return console.error("ChatPilot: siteKey and apiUrl are required"),null;const P=le(v,H),f=At(x),_=Lt({apiUrl:v,wsKey:I||"chatpilot-reverb-key",wsPort:C,forceTLS:p??v.startsWith("https")}),d=Pt({api:P,ws:_,i18n:f,position:c||"bottom-right"});return y==="dark"?(d.init(),d.setTheme("dark")):d.init(),P.getConfig().then(g=>{const S=g.data||g;d.setAdminOnline(!!S.admin_online),d.applySettings(S.settings),S.site_id&&_.subscribeSite(S.site_id)}).catch(()=>{}),_.connect(),re=d,d},destroy(){re&&(re.destroy(),re=null)},open(){re&&re.open()},close(){re&&re.close()}};return typeof window<"u"&&(window.ChatPilotWidget=rt),rt}));(function(){if(typeof window>"u")return;var J=document.currentScript||document.querySelector("script[data-site-key]");if(!J)return;var le=J.getAttribute("data-site-key"),Te=J.getAttribute("data-api");if(!le||!Te)return;function ge(){window.ChatPilotWidget.init({siteKey:le,apiUrl:Te,language:J.getAttribute("data-language")||void 0,position:J.getAttribute("data-position")||void 0,theme:J.getAttribute("data-theme")||void 0})}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",ge):ge()})();
