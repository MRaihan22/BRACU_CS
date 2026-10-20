import math
Min = -math.inf
Max = math.inf

def minmax(depth, node_idx, maximizing_player, values, alpha, beta):
    if depth == 3:
        return values[node_idx]

    if maximizing_player:
        best = Min
        for i in range(2):
            child_idx = node_idx * 2 + i
            value = minmax(depth + 1, child_idx, False, values, alpha, beta)
            best = max(best, value)
            alpha = max(alpha, best)

            if beta <= alpha:
                break

        return best

    else:
        best = Max
        for i in range(2):
            child_idx = node_idx * 2 + i
            value = minmax(depth + 1, child_idx, True, values, alpha, beta)
            best = min(best, value)
            best = min(beta, best)

            if beta <= alpha:
                break

        return best

values = [3, 5, 6, 9, 1, 2, 0, -1]

result = minmax(0, 0, True, values, Min, Max)

print("Optimal value =", result)