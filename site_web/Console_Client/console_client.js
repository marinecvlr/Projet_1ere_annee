function bascule(elem)
{
	// Quel est l'état actuel ?
	etat = document.getElementById(elem).style.visibility;
	
	if(etat=="hidden")
	{
		document.getElementById(elem).style.visibility="visible";
	}
	else
	{
		document.getElementById(elem).style.visibility="hidden";
	}
}