import { confirmDialog, toast } from '../../ui.js';
import { userService } from '../../services/userService.js';

export async function confirmAndDeleteUser(user, { onSuccess }) {
    const result = await confirmDialog({
        title: 'Excluir usuário?',
        text: `Deseja realmente excluir o usuário ${user.name}?`,
        confirmButtonText: 'Excluir',
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await userService.remove(user.id);

        await onSuccess();
        toast({
            icon: 'success',
            title: 'Sucesso',
            text: response.message || 'Usuário removido com sucesso.',
        });
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao excluir usuário',
            text: error.message || 'Não foi possível remover o usuário.',
        });
    }
}
