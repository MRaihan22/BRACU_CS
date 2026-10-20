def a_star_search(start, goal, graph, heuristics):
    visited = []
    # Each element:
    #     (parent, child, f_cost, g_cost)
    open_list = []
    path_cost = {start: 0}
    parent = {start: None}
    current = start
    while True:
        if current not in visited:
            visited.append(current)
            g = path_cost[current]
            for neighbour, cost in graph.get(current, []):
                g_new = g + cost
                h_new = heuristics.get(neighbour)
                f_new = g_new + h_new
                open_list.append((current, neighbour, f_new, g_new))

        open_list.sort(key = lambda x: x[2])
        parent_node, next_node, f_val, g_val = open_list.pop(0)

        if next_node == goal:
            parent[next_node] = parent_node
            path_cost[next_node] = g_val
            break

        if (
            next_node not in path_cost
            or 
            g_val < path_cost[next_node]
        ):
            parent[next_node] = parent_node
            path_cost[next_node] = g_val
        current = next_node

    path = []
    node = goal
    total_cost = path_cost.get(goal)

    while node is not None:
        path.insert(0, node)
        node = parent.get(node)
    return path, total_cost

heuristics = {
    "A": 366,
    "B": 374,
    "C": 329,
    "D": 244,
    "E": 253,
    "F": 178,
    "G": 193,
    "H": 98,
    "I": 0,
}

graph = {
    "A": [("B", 75), ("C", 118), ("E", 140)],
    "B": [],
    "C": [("D", 111)],
    "D": [],
    "E": [("G", 80), ("F", 99)],
    "F": [("I", 211)],
    "G": [("H", 97)],
    "H": [("I", 101)],
    "I": []
}

path, cost = a_star_search("A", "I", graph, heuristics)
print(path)
print("Total Cost =", cost)