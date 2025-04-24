# Sistema de Monitoramento de Criptomoedas

Este sistema foi desenvolvido utilizando Laravel com Blade no back-end e JavaScript no front-end para exibir informações de criptomoedas em uma interface gráfica moderna, responsiva e clean.

## Funcionalidades Implementadas

1. **Gráfico de histórico de preço da criptomoeda selecionada**
   - Interface com dropdown para seleção de criptomoedas
   - Gráfico interativo com toggle para alternar entre gráfico de linha e gráfico de velas (candlestick)
   - Seleção de intervalos de tempo: 1 dia, 1 semana, 1 mês e 1 ano
   - Gráficos renderizados com Chart.js

2. **Painéis de variação em tempo real**
   - Atualização automática a cada 10 segundos
   - Exibição das 5 criptomoedas com maior valorização nas últimas 24h (em verde)
   - Exibição das 5 criptomoedas com maior desvalorização nas últimas 24h (em vermelho)

3. **Design e arquitetura**
   - Interface moderna e responsiva utilizando TailwindCSS
   - Código organizado em camadas:
     - Requisições à API (via serviços Laravel)
     - Interface gráfica (Blade + JavaScript)
     - Componente de gráficos
   - Atualização em tempo real

## Tecnologias Utilizadas

- **Backend**: Laravel 10.x
- **Frontend**: Blade, JavaScript, TailwindCSS
- **Gráficos**: Chart.js
- **API**: CoinGecko

## Estrutura do Projeto

- `app/Services/CoinGeckoService.php`: Serviço para consumir a API do CoinGecko
- `app/Http/Controllers/CryptoController.php`: Controlador para gerenciar as requisições
- `resources/views/layouts/app.blade.php`: Layout base da aplicação
- `resources/views/crypto/index.blade.php`: Página principal com gráficos e painéis
- `routes/web.php`: Rotas da aplicação

## Instruções de Instalação

1. Extraia o arquivo zip em seu servidor
2. Configure o ambiente:
   ```bash
   cd crypto-monitor
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

3. Configure o arquivo `.env` com suas informações de ambiente

4. Compile os assets:
   ```bash
   npm run build
   ```

5. Inicie o servidor:
   ```bash
   php artisan serve
   ```

6. Acesse a aplicação em `http://localhost:8000`

## Considerações Adicionais

- O sistema utiliza cache para otimizar as requisições à API do CoinGecko
- A interface é totalmente responsiva, adaptando-se a diferentes tamanhos de tela
- Os gráficos são interativos e permitem visualizar informações detalhadas ao passar o mouse sobre os pontos
- A atualização automática dos painéis de variação garante informações sempre atualizadas

## Possíveis Melhorias Futuras

- Adicionar autenticação de usuários
- Implementar favoritos para acompanhamento de criptomoedas específicas
- Adicionar notificações para variações significativas de preço
- Expandir para mais tipos de gráficos e indicadores técnicos