# Code Challenge Grupo ZAP

## Objetivo
Refazer a API de consulta de imóveis elegíveis para **ZAP** e **Vivareal** utilizando a linguagem **PHP**,
de uma forma mais abstraída e de facil manutenção.

## 1. Requisitos

Para rodar essa API, deve se utilizar um servidor **Apache**.

Eu sempre costumo utilizar [**XAMPP**](https://www.apachefriends.org/pt_br/index.html) para todos os meus projetos em PHP, mas há outras ferramentas para isso ([**Wamp**](http://www.wampserver.com/en/), [**Mamp**](https://www.mamp.info/en/), entre outras). O importante mesmo é rodar tudo em um servidor Apache.

### Configuração inicial
O PHP é uma excelente linguagem para desenvolvimento WEB, porém não é necessariamente uma linguagem preparada nativamente para a construção de APIs de forma moderna como vemos em outras linguagens (NodeJS, por exemplo). Mas isso não é um problema, basta apenas configurar o ambiente de maneira adequada.

Para isso, é necessário acessar o arquivo **httpd.conf**, normalmente encontrado na raiz do Apache do servidor. **Pode variar dependendo da instalação.*

No caso deste projeto, ficaria em:
> C:\xampp\apache\conf\httpd.conf

Após encontrar o arquivo, é preciso localizar o módulo *modules/mod_rewrite.so*. Depois,  remover o comentário no início da linha e reiniciar o servidor. No final, deve ficar dessa forma:

Antes:

`#LoadModule rewrite_module modules/mod_rewrite.so`

Depois:

`LoadModule rewrite_module modules/mod_rewrite.so`

Basicamente, permite reescrever as URLs de uma maneira limpa e organizada.

## 2. Rodando localmente

### Chamada da API
A chamada da API deve ser feita da seguinte forma:

	URL: http://{{ambiente}}/{{projeto}}/listarImoveis
	Method: GET
	Params: 
		source: "ZAP|VIVA" -> *Obrigatório*
		pageNumber: int
		businessType: String
		bathrooms: int
		bedrooms: int
		usableAreas: int
		parkingSpaces: int
		listingType: String

* **{{ambiente}}**: localhost (ou IP equivalente). Caso o servidor esteja rodando em uma porta específica, deve-se informar também. No meu caso, fica *localhost:8081*.
* **{{projeto}}**: É a pasta raiz do projeto. Contudo, isso pode mudar quando o deploy for feito em produção. Exemplo no item 3.

O resultado deve ser algo parecido com isso:

	{
		"pageNumber": 0,
		"pageSize": 13,
		"totalCount": 13,
		"listings": [
			{
				"usableAreas": 69,
				"listingType": "USED",
				"createdAt": "2016-11-16T04:14:02Z",
				"listingStatus": "ACTIVE",
				"id": "a0f9d9647551",
				"parkingSpaces": 1,
				"updatedAt": "2016-11-16T04:14:02Z",
				"owner": false,
				.
				.
				.
	}

### Paginação de busca
Pensando no tempo de resposta do lado do cliente, eu preferi limitar o retorno dos resultados (chave *listings*) para 500 registros. Dessa forma, o cliente pode paginar seus resultados utilizando o parâmetro:
* **pageNumber**: Número da pagina desejada.

### Exemplos de chamadas
Alguns exemplos de filtragem abaixo:

* ZAP, Aluguel, 2 banheiros e 3 quartos 

		URL: http://localhost:8081/eng-zap-challenge-php/listarImoveis?source=ZAP&businessType=RENTAL&bathrooms=2&bedrooms=3

* VIVA, Venda, 1 banheiro e 1 vaga

		URL: http://localhost:8081/eng-zap-challenge-php/listarImoveis?source=VIVA&businessType=SALE&bathrooms=1&parkingSpaces=1

* SAP, Venda, 1 banheiro, 3 quartos e 1 vaga

		URL: http://localhost:8081/eng-zap-challenge-php/listarImoveis?source=ZAP&businessType=SALE&bathrooms=1&bedrooms=3&parkingSpaces=1

## 3. Deploy da aplicação
O deploy deve ser feito subindo os arquivos diretamente no servidor. 

Caso o projeto esteja na raiz do servidor, a variável *{{projeto}}* não estaria presente na URL. A chamada ficaria algo como:
	
	URL: http://grupozap.com.br/listarImoveis
	Method: GET
	Params: 
		source: "ZAP|VIVA" (Obrigatório)
		pageNumber: int
		pageSize: int
		businessType: String
		bathrooms: int
		bedrooms: int
		usableAreas: int
		parkingSpaces: int
		listingType: String

A ação não deve implicar na rota da API, pois o arquivo *.htacess* na raiz do projeto está configurado para reescrever a URL a partir do caminho *src\api\index.php*.

## 4. Especificações tecnológicas

Há diversos frameworks *Open Source* disponíveis na Internet para a construção de APIs em PHP. 
Como tenho mais experiência em desenvolver APIs em NodeJS, construí uma Lib chamada **CallbackApi**.

Busquei construir a sintaxe parecida com uma chamada de API em NodeJS.

O intuito da lib é englobar alguns métodos importantes de validação e execução, parâmetros de request, response, etc.

Além disso, a construi de maneira independente das regras de negócio do projeto. Assim, fica fácil utiliza-lá em outros projetos se necessário.

No entanto, como o foco do projeto é apenas construir uma API que retorne os dados necessários para o cliente, preferi não implementar os métodos *POST*, *PUT* ou *DELETE*, por exemplo.

## 5. Validação de erros
A Lib contém as seguintes tratativas:
* Tipo de request inválido

		Motivo: Request informado é diferente do request correto.
		Código do erro: 001
		Mensagem: "Invalid request type."

* Origem desconhecida

		Motivo: O parâmetro "source" informado é diferente de ZAP ou VIVA
		Código do erro: 002
		Mensagem: "Invalid source data (ZAP/VIVA)"

## 6. Construído com
* [PHP 7.1](https://www.php.net/) - Linguagem de programação
* [Apache/Xampp](https://www.apachefriends.org/pt_br/index.html) - Servidor para rodar o PHP

## 7. Autor
Pedro Melo | vieirapcm@gmail.com | (11) 97611-1799
* [LinkedIn](https://www.linkedin.com/in/vieirapcm/)
* [Github](https://github.com/vieirapcm)
