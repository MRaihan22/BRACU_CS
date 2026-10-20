import random
import math

# =========== Basic Settings ===========
gridSize = 25
populationSz = 6
maxGens = 15
mutRate = 0.08
elitism = 2
plateauLimit = 5 #If the best solution does not improve for 5 consecutive generations, stop.

Alpha = 1000
Beta = 2
Gamma =1


# =========== Block Infos ===========
Blocks = [
    {"Name": "ALU", "Width": 5, "Height": 5},
    {"Name": "Cache", "Width": 7, "Height": 4},
    {"Name": "Control Unit", "Width": 4, "Height": 4},
    {"Name": "Register File", "Width": 6, "Height": 6},
    {"Name": "Decoder", "Width": 5, "Height": 3},
    {"Name": "Floating Unit", "Width": 5, "Height": 5},
]

Connections = [
    ("Register File", "ALU"),
    ("Control Unit", "ALU"),
    ("ALU", "Cache"),
    ("Register File", "Floating Unit"),
    ("Cache", "Decoder"),
    ("Decoder", "Floating Unit"),
]

Name_To_Idx = {
    block["Name"]: idx
    for idx, block in enumerate(Blocks)
}


# =========== Creating one random gene ===========
def random_gene(block_idx):
    block = Blocks[block_idx]
    max_x = gridSize - block["Width"]
    max_y = gridSize - block["Height"]

    x = random.randint(0, max_x)
    y = random.randint(0, max_y)

    return (x, y)


# =========== CREATE ONE RANDOM CHROMOSOME ===========
def random_chromosome():
    chromosome = []
    for block_idx in range(len(Blocks)):
        chromosome.append(random_gene(block_idx))
    return chromosome


# =========== CREATE THE INITIAL POPULATION ===========
def create_initial_population():
    population = []
    for _ in range(populationSz):
        population.append(random_chromosome())
    return population