// Runs before the page paints so dark mode does not flash. Kept as a file (not inline) for the Content-Security-Policy.
try{var m=localStorage.getItem('theme')||'system';if(m==='dark'||(m==='system'&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}
