<?php
return [
    'error' => 'Erro',
    'description' => 'Descrição',
    'resolution' => 'Resolução',
    
    'ERR001' => [
        'message' => 'Por favor, selecione o balcão primeiro!',
        'description' => 'O usuário tentou uma ação sem selecionar um balcão.',
        'resolution' => 'Solicite ao usuário que escolha um balcão antes de prosseguir.',
    ],
    'ERR002' => [
        'message' => 'Nenhuma chamada',
        'description' => 'Nenhuma chamada atual para realizar a operação.',
        'resolution' => 'Inicie ou aguarde uma nova chamada.',
    ],
    'ERR003' => [
        'message' => 'Feche a chamada em andamento primeiro!',
        'description' => 'O usuário deve encerrar a chamada ativa antes de iniciar uma nova.',
        'resolution' => 'Feche a chamada atual antes de prosseguir.',
    ],
    'ERR004' => [
        'message' => 'Esta chamada está temporariamente em espera',
        'description' => 'A chamada selecionada está temporariamente em espera.',
        'resolution' => 'Retome a chamada antes de continuar.',
    ],
    'ERR005' => [
        'message' => 'Esta chamada está temporariamente em espera (início)',
        'description' => 'A chamada em fila entrou em estado de espera temporária.',
        'resolution' => 'Aguarde até que a espera seja removida ou retome manualmente.',
    ],
    'ERR006' => [
        'message' => 'O número da fila já existe',
        'description' => 'O número da fila já existe no sistema.',
        'resolution' => 'Gere um novo número de fila exclusivo.',
    ],
    'ERR007' => [
        'message' => "O sistema não possui uma chamada que está sendo atendida por você!",
        'description' => 'Nenhuma chamada ativa encontrada atribuída ao usuário.',
        'resolution' => 'Certifique-se de que uma chamada está sendo atendida.',
    ],
    'ERR008' => [
        'message' => "O número da fila já existe",
        'description' => 'O número da fila já existe no banco de dados',
        'resolution' => 'Por favor, verifique seu número de fila',
    ],
    'ERR009' => [
        'message' => "A fila atual foi redefinida e não foi fechada!",
        'description' => 'A fila atual foi redefinida',
        'resolution' => 'Sua fila foi redefinida',
    ],
    
    'BOOK001' => [
        'message' => 'Não foi possível gerar o ticket devido a regras inválidas.',
        'description' => 'A configuração do sistema bloqueou a criação do ticket.',
        'resolution' => 'Entre em contato com o administrador para revisar as regras de agendamento.',
    ],
    'BOOK002' => [
        'message' => 'Pagamento falhou: Algo deu errado',
        'description' => 'Ocorreu um problema desconhecido durante o pagamento.',
        'resolution' => 'Tente o pagamento novamente ou entre em contato com o suporte.',
    ],
    'BOOK003' => [
        'message' => 'Chaves do serviço de pagamento ausentes',
        'description' => 'As credenciais da API de pagamento não estão configuradas.',
        'resolution' => 'Configure a chave e o segredo da API nas configurações.',
    ],
    'BOOK004' => [
        'message' => 'Configuração de pagamento não concluída',
        'description' => 'As configurações de pagamento estão incompletas.',
        'resolution' => 'Conclua a configuração de pagamento no painel de administração.',
    ],

    'SUCCESS001' => [
        'message' => 'Chamada realizada com sucesso',
    ],
    'SUCCESS002' => [
        'message' => 'Suspensão processada com sucesso e notificações enviadas',
    ],
    'SUCCESS003' => [
        'message' => 'Chamada iniciada com sucesso'
    ],
    'SUCCESS004' => [
        'message' => 'Chamada encerrada com sucesso'
    ],
    'SUCCESS005' => [
        'message' => 'Transferência da chamada realizada com sucesso'
    ],
    'SUCCESS006' => [
        'message' => 'Rechamada realizada com sucesso'
    ],
    'SUCCESS007' => [
        'message' => 'Retorno da chamada realizado com sucesso'
    ],
    'SUCCESS008' => [
        'message' => 'Solicitação enviada ao administrador'
    ],
    'SUCCESS009' => [
        'message' => 'Espera realizada com sucesso'
    ],
    'SUCCESS0010' => [
        'message' => 'Cancelamento realizado com sucesso'
    ],
    'SUCCESS0011' => [
        'message' => 'SMS enviado com sucesso!'
    ],
    'SUCCESS0012' => [
        'message' => 'Fila gerada com sucesso!'
    ],
    'SUCCESS0013' => [
        'message' => 'Nota de estimativa atualizada com sucesso!'
    ],
    'SUCCESS0014' => [
        'message' => 'Reversão da chamada realizada com sucesso'
    ],
    'SUCCESS0015' => [
        'message' => 'Visitante editado com sucesso'
    ],
    'SUCCESS0016' => [
        'message' => 'Chamada perdida registrada com sucesso'
    ],


    'VAL001' => [
        'message' => 'Por favor, insira o número da fila e a categoria',
    ],
    'VAL002' => [
        'message' => 'Por favor, insira o tipo de pausa e o comentário',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => 'Clique no botão continuar para desbloquear esta tela! O tempo de pausa é de',
    'minutes.' => 'minutos.',
    'CONTINUE' => 'CONTINUAR',
    'Call started Successfully' => 'Chamada iniciada com sucesso',
    'success' => 'sucesso',
    'Suspension processed successfully with notifications sent' => 'Suspensão processada com sucesso e notificações enviadas',
    'Are you sure' => 'Você tem certeza',
    'warning' => 'aviso',
    'You want to revert this' => 'Você quer reverter isto',
    'YES, REVERT IT' => 'SIM, REVERTER',
    'No, CANCEL' => 'Não, CANCELAR',
    'Please rate our service' => 'Por favor, avalie nosso serviço',
    'Excellent' => 'Excelente',
    'Good' => 'Bom',
    'Neutral' => 'Neutro',
    'Poor' => 'Ruim',
    'Please Wait' => 'Por favor, aguarde',
    'Revert Queue' => 'Reverter fila',
    'Cancelled' => 'Cancelado',
    'Your data is safe' => 'Seus dados estão seguros',
    'error' => 'erro',
    "You won't be able to revert this" => "Você não poderá reverter isto",
    'OK' => 'OK',
    'Cancel' => 'Cancelar',
    'Please enter queue number and category' => 'Por favor, insira o número da fila e a categoria',
    'Break' => 'Pausa',
    'Choose Any Reason' => 'Escolha qualquer motivo',
    'Comment' => 'Comentário',
    'Please enter break type and comment' => 'Por favor, insira o tipo de pausa e o comentário',
    'Enter Queue Number' => 'Insira o número da fila',
    'Select Category' => 'Selecione a categoria',
    'Type of Break' => 'Tipo de pausa',
    'Unlock Screen' => 'Desbloquear tela',
    'Updating' => 'Atualizando'
];
