# ✈️ Onfly
<br>
<br>

## 🚀 Como rodar o projeto (com Docker)

Este projeto utiliza **Docker** para facilitar a instalação e execução.  
Mesmo que você não tenha experiência prévia, é só seguir os passos abaixo:

<br>

## 🛠️ Requisitos

- Ter o **Docker** instalado no computador.
- Ter o **Docker Compose** disponível (geralmente já vem junto com o Docker Desktop).

<br>

## 📦 Instruções para executar

### 1. Clone o repositório

Abra o terminal dentro da pasta desejada e clone o projeto:

```bash
git clone https://github.com/Gabriell-Braga/onfly.git
```

Depois entre na pasta do projeto com o seguinte comando:

```bash
cd onfly
```

### 2. Configure variáveis de ambiente

Faça a cópia do arquivo .env.example para o .env:

```bash
cp onfly-backend/.env.example onfly-backend/.env
```

Se estiver usando PowerShell, use:

```bash
Copy-Item onfly-backend/.env.example onfly-backend/.env
```

### 3. Suba o projeto com Docker

Agora escolha uma das opções de comando abaixo:

#### ➡️ Subir o projeto **zerando o banco de dados**

Se você quiser limpar o banco e começar do zero (⚠️ RODE ESSA NA PRIMEIRA VEZ EXECUTANDO O PROJETO):

```bash
$env:DB_REFRESH="true"; docker-compose up --build
```

#### ➡️ Subir o projeto **mantendo os dados existentes**

Se quiser iniciar o projeto mantendo os dados anteriores:

```bash
$env:DB_REFRESH="false"; docker-compose up --build
```

⚠️ FAÇA SEMPRE O PASSO 4 E 5 APÓS ESTE PASSO, É EXTREMAMENTE NECESSÁRIO TER CONFIGURADO AS VARIÁVEIS DE AMBIENTE PARA O FUNCIONAMENTO DO PROJETO

### 4. Configure variáveis de ambiente

Abra outro terminal de comandos enquanto o projeto estiver rodando com o Docker e execute o seguinte comando:

```bash
docker exec -it onfly-backend bash
```

Dentro do container, execute:

```bash
php artisan jwt:secret
```
Confirme o override.

### 5. Limpar e recachear as configurações do Laravel

Ainda dentro do container execute:

```bash
php artisan config:clear
```

Depois:

```bash
php artisan cache:clear
```

E por último:

```bash
php artisan config:cache
```

### 6. Acessando a aplicação

Depois que tudo estiver rodando, acesse:

| Serviço           | Endereço para acessar                          |
|-------------------|------------------------------------------------|
| API Back-end      | [http://localhost:8000](http://localhost:8000) |
| Front-end         | [http://localhost:4173](http://localhost:4173) |
| Banco de dados    | (MySQL interno no Docker, já configurado)      |

### 7. O que é `$env:DB_REFRESH`?

Essa variável controla se o banco de dados deve ser resetado:

- `$env:DB_REFRESH="true"` → Apaga e recria as tabelas (útil para testes), executando os migrations.
- `$env:DB_REFRESH="false"` → Mantém o banco atual, pulando a execução dos migrations para evitar perda de dados.

**Importante:**  
- O back-end detecta esse valor para decidir se roda as migrações novamente ao iniciar.

### 8. Observações finais

- A primeira execução pode demorar um pouco, pois o Docker precisará baixar as imagens e instalar dependências.
- Aguarde todos os containers estarem prontos no terminal antes de acessar no navegador.
- Para parar o projeto, pressione `CTRL+C` no terminal e depois execute:

```bash
docker-compose down
```

<br>

## 🛫 Funcionamento do Sistema

O projeto é uma plataforma de **gestão de pedidos de viagem corporativa**.  
Funciona de maneira simples, intuitiva e segura.

### 👤 Acesso Administrativo

O sistema já possui um **usuário administrador** criado automaticamente para facilitar o acesso inicial:

| Função  | Informação          |
|---------|---------------------|
| E-mail  | `admin@onfly.com`   |
| Senha   | `admin123`          |

✅ Este usuário possui **acesso completo** às funcionalidades administrativas, como aprovação e cancelamento de pedidos de viagem.

### 🔄 Fluxo Básico de Utilização

1. **Login:**
   - O usuário acessa a tela de login e informa suas credenciais (e-mail e senha).
   - Após autenticação, o token JWT é armazenado para proteger o acesso às rotas internas.

2. **Registro de Novos Usuários:**
   - O sistema permite que novos usuários se cadastrem criando sua própria conta.
   - Para isso, basta acessar a opção de registro e preencher: nome, e-mail e senha.
   - Após o cadastro, o usuário já pode realizar login normalmente para criar e gerenciar seus pedidos de viagem.

3. **Criação de Pedidos de Viagem:**
   - Usuários autenticados podem criar novos pedidos de viagem através de um formulário dedicado.
   - Cada pedido inclui: nome do solicitante, destino, data de ida, data de volta e status inicial ("Solicitado").
   - Os campos do formulário possuem validações obrigatórias para garantir o preenchimento correto.

4. **Visualização, Filtros e Ordenação:**
   - Os pedidos são listados em uma **tabela interativa**, que permite aplicar filtros por **status** (solicitado, aprovado, cancelado), **período** (faixa de datas) e **destino**.
   - A tabela também permite **ordenar** os dados clicando nos cabeçalhos das colunas (como nome, destino ou data).
   - Essa interação facilita a busca e a organização dos pedidos, tornando a navegação rápida e eficiente.

5. **Atualização de Status:**
   - Um administrador pode aprovar ou cancelar pedidos diretamente pela tabela.
   - Ao atualizar o status de um pedido, uma notificação automática é gerada e enviada para o usuário que fez o pedido.

6. **Notificações:**
   - O sistema realiza **verificações periódicas** para exibir novas notificações de forma automática, informando o usuário sobre aprovações ou cancelamentos de suas viagens.

7. **Responsividade e Experiência do Usuário:**
   - A plataforma é totalmente responsiva, adaptando-se perfeitamente a celulares, tablets e desktops.
   - Foram implementadas animações de transição entre telas, animações no cabeçalho ao scrollar, animações de ping para novos alertas e spinners de carregamento, proporcionando uma experiência moderna, agradável e intuitiva.


<br>

## 🧪 Como rodar os testes unitários do Back-end

O projeto conta com uma suíte de testes automatizados para garantir a qualidade e a confiabilidade das principais funcionalidades da API.

### 📋 Instruções para rodar os testes

1. **Abra outro terminal** (o projeto precisa estar em execução pelo docker) dentro da pasta raiz do projeto, execute:

```bash
docker exec -it onfly-backend bash
```

2. **Dentro do container**, execute o comando de testes do Laravel:

```bash
php artisan test
```

✅ O sistema irá rodar automaticamente todos os testes unitários e exibir o resultado diretamente no terminal.

<br>

## 🧠 Sobre os Testes Implementados

Os testes cobrem as funcionalidades mais importantes do sistema de viagens:

- **Registro de Usuário:**  
  Verifica se novos usuários podem se registrar corretamente pela API.

- **Login de Usuário:**  
  Garante que usuários cadastrados conseguem fazer login e receber um token JWT.

- **Criação de Pedido de Viagem:**  
  Testa se um usuário autenticado pode criar novos pedidos de viagem.

- **Listagem de Viagens:**  
  - Usuário comum lista apenas **suas próprias viagens**.
  - Administrador lista **todas as viagens** do sistema.

- **Alteração de Status de Viagens:**  
  - Apenas administradores podem aprovar ou cancelar viagens.
  - Usuários comuns são bloqueados de alterar o status.

- **Restrições de Acesso:**  
  - Um usuário **não consegue visualizar** pedidos de outro usuário.
  - Um administrador **pode acessar** pedidos de qualquer usuário.

- **Filtros de Listagem:**  
  - Filtrar viagens por **status** (aprovado, cancelado, solicitado).
  - Filtrar viagens por **destino** (busca textual parcial).

- **Notificações:**  
  - Usuário pode visualizar suas notificações.
  - Usuário pode marcar notificações como lidas.
  - Usuário pode excluir notificações específicas.

- **Cancelamento de Viagens Passadas:**  
  Garante que uma viagem já realizada **não pode ser cancelada** (regra de negócio).

- **Refresh de Token:**  
  Testa o fluxo de renovação do token JWT para manter a sessão do usuário ativa.

✅ Com essa bateria de testes, garantimos que a aplicação está funcionando corretamente em seus principais fluxos de autenticação, registro de pedidos, notificações e regras de permissão.  
✅ Também asseguramos que erros críticos de segurança (como acesso não autorizado) estão sendo tratados.

<br>

## 🧠 Considerações Técnicas do Projeto

Durante o desenvolvimento deste projeto, algumas decisões técnicas foram tomadas de forma consciente para equilibrar a qualidade da solução com o escopo proposto no desafio.

- **Sistema de Notificações:**  
  Optei por utilizar uma **tabela de notificações** atualizada via **requisições periódicas** (polling) ao back-end.  
  Essa escolha se deu porque o projeto é **local e de pequeno porte**, sem necessidade de escalabilidade em tempo real.  
  Entretanto, tenho pleno conhecimento de que, em ambientes de produção com alto volume de atualizações, a abordagem ideal seria a implementação de **WebSockets**, proporcionando notificações em tempo real e maior eficiência na comunicação entre cliente e servidor.

- **Design Responsivo e UX/UI:**  
  Construi o front-end seguindo princípios de **responsividade** e boas práticas de **UX/UI**.  
  A interface foi desenhada para ser **intuitiva, acessível e visualmente agradável**, garantindo fácil compreensão e navegação para qualquer tipo de usuário, independentemente do dispositivo utilizado.

- **Animações e Interatividade:**  
  Para enriquecer a experiência do usuário, implementei:
  - **Animações de transição** suaves entre páginas e componentes.
  - **Animação no header** durante o scroll, proporcionando uma navegação mais fluida e moderna.
  - **Animações de "ping"** para destacar elementos dinâmicos e novos alertas de notificação.
  - **Loading spinners** para indicar carregamentos de forma clara e manter o usuário informado sobre as ações assíncronas em andamento.

  Esses elementos visam não apenas embelezar a aplicação, mas também criar uma interação **mais fluida, informativa e agradável**, melhorando significativamente a percepção de qualidade da plataforma.

<br>

## 🗂️ Organização do Repositório GitHub

Devido ao tempo limitado para a entrega do desafio, o controle de versões (Git) foi utilizado de forma mais direta, com foco total no desenvolvimento funcional do projeto.

Caso tivesse mais tempo disponível, faria uma organização ainda mais robusta do repositório, incluindo:

- **Commits mais granulares e descritivos:**  
  Evitaria o chamado **commit bombing** (múltiplos commits genéricos em sequência) e estruturaria as mensagens de commit seguindo boas práticas, como:
  - Separar claramente implementações novas de correções de bugs.
  - Mensagens mais detalhadas, explicando o que foi alterado e o motivo.

- **Branches organizadas:**  
  Criaria branches específicas para novas features, correções e melhorias, utilizando uma estrutura de versionamento semelhante a fluxos como Git Flow ou GitHub Flow.

- **Pull Requests:**  
  Implementaria PRs mesmo para projetos solo, garantindo melhor controle de revisão e integração contínua.

- **Tags e Releases:**  
  Versões importantes do projeto seriam marcadas com **tags** e **releases**, facilitando a rastreabilidade das entregas.
