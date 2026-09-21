window.addEventListener('load', () =>
{
	const container = document.querySelector('.container');
	if (container)
	{
		const newDiv = document.createElement("div");
		newDiv.classList.add('container');
		const newContent = document.createTextNode("BONJOUR, CE SITE A ETE HACKE PAR..... TOI, TU VAS ETRE POURSUIVIE EN JUSTICE POUR CET AFRONT !");
		newDiv.appendChild(newContent);
		document.body.insertBefore(newDiv, container);
		container.classList.add('d-none');
	}
});